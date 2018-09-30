<?php

use Ramsey\Uuid\Uuid;

class BinaryUuid extends Benchmark
{
    public function name(): string
    {
        return 'Binary UUID';
    }

    public function createTable()
    {
        $this->connection->exec(<<<'SQL'
DROP TABLE IF EXISTS `binary_uuid`;

CREATE TABLE `binary_uuid` (
    `uuid` BINARY(16) PRIMARY KEY,
    `text` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
    }

    public function seedTable()
    {
        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $uuid = str_replace('-', '', Uuid::uuid1()->toString());

            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $query = $this->connection->prepare('INSERT INTO `binary_uuid` (`uuid`, `text`) VALUES (:uuid, :text)');
            $query->bindValue('uuid', $uuid, \PDO::PARAM_STR);
            $query->bindValue('text', $i . ' ' . $text, \PDO::PARAM_STR);

            $query->execute();
        }
    }

    public function run(): InlineResult
    {
        $queries = [];
        $uuids = $this->connection->fetchAll('SELECT `uuid` FROM `binary_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['uuid'];

            $query = $this->connection->prepare('SELECT * FROM `binary_uuid` WHERE `uuid` = :uuid');
            $query->bindParam('uuid', $uuid, \PDO::PARAM_STR);

            $queries[] = $query;
        }

        return $this->runQueryBenchmark($queries);
    }
}