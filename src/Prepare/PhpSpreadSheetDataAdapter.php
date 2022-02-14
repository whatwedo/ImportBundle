<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Prepare;

use PhpOffice\PhpSpreadsheet\IOFactory;

class PhpSpreadSheetDataAdapter implements DataAdapterInterface
{
    public function prepare($fileName): array
    {
        $spreadsheet = IOFactory::load($fileName);

        $sheet = $spreadsheet->getSheet(0);
        $headers = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1')[0];
        $data = $sheet->rangeToArray('A2:' . $sheet->getHighestColumn() . $sheet->getHighestRow($sheet->getHighestColumn()));

        $rows = [];
        foreach ($data as $line) {
            $rowItem = [];
            foreach ($headers as $headerIndex => $headerKey) {
                $rowItem[$headerKey] = (string) $line[$headerIndex];
            }
            $rows[] = $rowItem;
        }

        // make mulitdimensional
        $rows = $this->multiDimensional($rows);

        return $rows;
    }

    private function multiDimensional(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $firstCell = $row[array_keys($row)[0]];
            if ($firstCell === '') {
                $lastResultRow = array_pop($result);

                foreach ($row as $key => $value) {
                    if ($value !== '') {
                        if (! is_array($lastResultRow[$key])) {
                            $lastResultRow[$key] = [$lastResultRow[$key]];
                        }
                        $lastResultRow[$key][] = $value;
                    }
                }

                $result[] = $lastResultRow;
            } else {
                $result[] = $row;
            }
        }

        return $result;
    }
}
