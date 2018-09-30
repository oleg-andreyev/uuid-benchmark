<?php

class NormalId extends Benchmark
{
    public function name(): string
    {
        return 'Normal ID';
    }

    public function createTable()
    {
        $this->connection->exec(<<<'SQL'
DROP TABLE IF EXISTS `normal_id`;

CREATE TABLE `normal_id` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `text` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
    }

    public function seedTable()
    {
        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $text = $this->randomTexts[array_rand($this->randomTexts)];


            $query = $this->connection->prepare('INSERT INTO `normal_id` (`text`) VALUES (:text)');
            $query->bindValue('text', $i . ' ' . $text, \PDO::PARAM_STR);

            $query->execute();
        }


        write("\r$i of $this->recordsInTable");
        writeln("");
    }

    public function run(): InlineResult
    {
        $queries = [];
        $ids = $this->connection->fetchAll('SELECT `id` FROM `normal_id`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $id = $ids[array_rand($ids)]['id'];

            $query = $this->connection->prepare('SELECT * FROM `normal_id` WHERE `id` = :id');
            $query->bindParam('id', $id);

            $queries[] = $query;
        }

        return $this->runQueryBenchmark($queries);
    }
}