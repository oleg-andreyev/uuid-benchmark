<?php

class FileResult
{
    public static function save(Benchmark $benchmark)
    {
        $slug = str_replace(' ', '-', strtolower($benchmark->name()))."_{$benchmark->recordsInTable()}";
        $path = __DIR__."/data/{$slug}.csv";
        $handle = fopen($path, 'w+');
        foreach ($benchmark->result() as $executionTime) {
            fputcsv($handle, ['executionTime' => number_format($executionTime, 23)]);
        }
        fclose($handle);
    }
}