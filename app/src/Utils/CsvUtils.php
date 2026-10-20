<?php

declare(strict_types=1);

namespace App\Utils;

final class CsvUtils
{
    public static function csvWithHeaderToArray(string $csvContent): array
    {
        $csvContentSplit = preg_split("/((\r?\n)|(\r\n?))/", $csvContent);
        if (!is_array($csvContentSplit)) {
            return [];
        }

        $keys = str_getcsv((string) array_shift($csvContentSplit));

        $csvArray = [];
        foreach ($csvContentSplit as $line) {
            if (empty($line)) {
                continue;
            }

            $csvArray[] = array_combine($keys, str_getcsv($line));
        }

        return $csvArray;
    }
}
