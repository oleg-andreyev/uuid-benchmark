<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class OptimisedUuid extends Benchmark
{
    public function name(): string
    {
        return 'Optimised UUID';
    }

    public function createTable()
    {
        $this->connection->exec(<<<'SQL'
DROP TABLE IF EXISTS `optimised_uuid`;

CREATE TABLE `optimised_uuid` (
    `uuid` BINARY(16) PRIMARY KEY,
    `text` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
    }

    public function seedTable()
    {
        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $uuid = Uuid::uuid1();

            $encodedUuid = $this->encodeBinary($uuid);

            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $query = $this->connection->prepare('INSERT INTO `optimised_uuid` (`uuid`, `text`) VALUES (:uuid,:text)');
            $query->bindValue('uuid', $encodedUuid, \PDO::PARAM_STR);
            $query->bindValue('text', $i . ' ' . $text, \PDO::PARAM_STR);

            $query->execute();

        }
    }

    public function run(): InlineResult
    {
        $queries = [];
        $uuids = $this->connection->fetchAll('SELECT `uuid` FROM `optimised_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['uuid'];

            $query = $this->connection->prepare('SELECT * FROM `optimised_uuid` WHERE `uuid` = :uuid');
            $query->bindParam('uuid', $uuid, \PDO::PARAM_STR);

            $queries[] = $query;
        }

        return $this->runQueryBenchmark($queries);
    }

    protected function encodeBinary(UuidInterface $uuid): string
    {
        $fields = $uuid->getFieldsHex();

        $optimized = [
            $fields['time_hi_and_version'],
            $fields['time_mid'],
            $fields['time_low'],
            $fields['clock_seq_hi_and_reserved'],
            $fields['clock_seq_low'],
            $fields['node'],
        ];

        return hex2bin(implode('', $optimized));
    }
}