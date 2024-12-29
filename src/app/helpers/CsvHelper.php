<?php

namespace src\helpers;

class CsvHelper
{
    /**
     * @param $csvPath
     * @return array
     */
    public static function parse($csvPath): array
    {
        $csv = file_get_contents($csvPath);

        return array_map(function ($line) {
            $parts = str_getcsv($line);

            return [
                'phone_number' => trim($parts[0]),
                'name' => trim($parts[1]),
            ];
        }, str_getcsv($csv, "\n"));
    }

    public static function isValidCsvFile(array $csv): bool
    {
        return strtolower(pathinfo($csv['name'], PATHINFO_EXTENSION)) == 'csv';
    }
}