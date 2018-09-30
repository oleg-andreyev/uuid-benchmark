<?php

use Ramsey\Uuid\Uuid;

class TextualUuid extends Benchmark
{
    public function name(): string
    {
        return 'Textual UUID';
    }

    public function createTable()
    {
        $this->connection->exec(<<<'SQL'
DROP TABLE IF EXISTS `textual_uuid`;

CREATE TABLE `textual_uuid` (
    `uuid` CHAR(36) PRIMARY KEY,
    `text` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
    }

    public function seedTable()
    {
        $queries = [];

        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $uuid = Uuid::uuid1()->toString();

            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $query = $this->connection->prepare('INSERT INTO `textual_uuid` (`uuid`, `text`) VALUES (:uuid,:text)');
            $query->bindValue('uuid', $uuid, \PDO::PARAM_STR);
            $query->bindValue('text', $i . ' ' . $text, \PDO::PARAM_STR);

            $query->execute();
        }
    }

    public function run(): InlineResult
    {
        $queries = [];
        $uuids = $this->connection->fetchAll('SELECT `uuid` FROM `textual_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['uuid'];

            $query = $this->connection->prepare('SELECT * FROM `textual_uuid` WHERE `uuid` = :uuid');
            $query->bindParam('uuid', $uuid, \PDO::PARAM_STR);

            $queries[] = $query;
        }

        return $this->runQueryBenchmark($queries);
    }
}