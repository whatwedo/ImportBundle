<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Prepare;

class TextDataAdapter implements DataAdapterInterface
{
    private string $lineDelimiter;

    private string $cellDeliiter;

    private string $textDelimiter;

    public function __construct(string $lineDelimiter, string $cellDeliiter, string $textDelimiter)
    {
        $this->cellDeliiter = $cellDeliiter;
        $this->textDelimiter = $textDelimiter;
        $this->lineDelimiter = $lineDelimiter;
    }

    public function prepare($data): array
    {
        $headers = [];
        $rows = [];

        $lines = explode($this->lineDelimiter, $data);

        if (isset($lines[0])) {
            $headers = explode($this->cellDeliiter, trim($lines[0]));
            $headers = array_map(fn ($item) => $this->cleanDelimiter($item), $headers);
        }

        for ($i = 1; $i < count($lines); ++$i) {
            $rowItem = [];
            $line = $lines[$i];
            if ($line !== '') {
                $line = explode($this->cellDeliiter, trim($line));
                $line = array_map(fn ($item) => $this->cleanDelimiter($item), $line);

                foreach ($headers as $headerIndex => $headerKey) {
                    $rowItem[$headerKey] = $line[$headerIndex];
                }
                $rows[] = $rowItem;
            }
        }

        // make mulitdimensional
        $rows = $this->multiDimensional($rows);

        return $rows;
    }

    private function cleanDelimiter($item)
    {
        return trim($item, $this->textDelimiter);
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
