<?php

require_once './vendor/autoload.php';

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Debug\Debug;

Debug::enable(-1, true);

$config = new Configuration();
$parameters = [
    'dbname' => getenv('DB_NAME'),
    'user' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'host' => getenv('DB_HOST'),
    'driver' => 'pdo_mysql',
];
$connection = DriverManager::getConnection($parameters, $config);

function determineIterations(): array
{
    $max = getenv('RECORDS_IN_TABLE');
    return array_filter([
        1000,
        50000,
        500000,
    ], function ($recordsInTable) use ($max) {
        return $recordsInTable <= $max;
    });
}

function runIteration(Collection $benchmarks, int $recordsInTable)
{
    $benchmarks
        ->forAll(function ($i, Benchmark $benchmark) use ($recordsInTable) {
            $benchmark
                ->withRecordsInTable($recordsInTable)
                ->withBenchmarkRounds(getenv('BENCHMARK_ROUNDS'))
                ->withFlushAmount(getenv('FLUSH_QUERY_AMOUNT'));

            writeln("\nCreating tables");

            $benchmark->createTable();
            writeln("\t- {$benchmark->name()}");

            writeln("\nSeeding tables");

            $benchmark->seedTable();
            writeln("\t- {$benchmark->name()}");

            writeln("\nRunning benchmarks");

            writeln("\t- {$benchmark->name()}: ");
            $result = $benchmark->run();
            writeln("\t\tAvarage of {$result->getAverageInMilliSeconds()}ms over {$result->getIterations()} iterations.");
        })
    ;
}

function writeln($msg)
{
    write($msg . PHP_EOL);
}

function write($msg)
{
    echo $msg;
}

$benchmarks = new ArrayCollection([
    new NormalId($connection),
    new BinaryUuid($connection),
    new OptimisedUuid($connection),
    new TextualUuid($connection),
]);

writeln('Starting benchmarks...');
$iterations = determineIterations();
foreach ($iterations as $iteration => $recordsInTable) {
    writeln("\nStarting iteration {$iteration} with {$recordsInTable} records in table");
    runIteration($benchmarks, $recordsInTable);
}
writeln("\nDone");