<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\ImportBundle\Prepare\PhpSpreadSheetDataAdapter;

class PhpSpreadsheetDataAdapterTest extends KernelTestCase
{
    public function testDataAdapter()
    {
        $spreadsheetFile = __DIR__ . '/data/spreadSheetAdapterTest.xlsx';

        $dataAdapter = new PhpSpreadSheetDataAdapter();

        $result = $dataAdapter->prepare($spreadsheetFile);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        foreach ($result as $row) {
            $this->assertCount(4, $row);
            $this->assertArrayHasKey('Header1', $row);
            $this->assertArrayHasKey('Header2', $row);
            $this->assertArrayHasKey('Header 3', $row);
            $this->assertArrayHasKey('Header 4', $row);
        }

        $this->assertSame('data11', $result[0]['Header1']);
        $this->assertSame('data12', $result[0]['Header2']);
        $this->assertSame('data13', $result[0]['Header 3']);
        $this->assertSame('Data 14', $result[0]['Header 4']);

        $this->assertSame('1', $result[1]['Header1']);
        $this->assertSame('2', $result[1]['Header2']);
        $this->assertSame('3', $result[1]['Header 3']);
        $this->assertSame('4', $result[1]['Header 4']);

        $this->assertSame('Data 1', $result[2]['Header1']);
        $this->assertSame('Data 2', $result[2]['Header2']);
        $this->assertSame('Data 3', $result[2]['Header 3']);
        $this->assertSame('Data 4', $result[2]['Header 4']);
    }

    public function testDataAdapterMultidimensional()
    {
        $spreadsheetFile = __DIR__ . '/data/spreadSheetAdapterMultiDimensionalTest.xlsx';

        $dataAdapter = new PhpSpreadSheetDataAdapter();

        $result = $dataAdapter->prepare($spreadsheetFile);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        foreach ($result as $row) {
            $this->assertCount(5, $row);
            $this->assertArrayHasKey('Header1', $row);
            $this->assertArrayHasKey('Header2', $row);
            $this->assertArrayHasKey('Header 3', $row);
            $this->assertArrayHasKey('Header 4', $row);
            $this->assertArrayHasKey('Header multidimensional', $row);
        }

        $this->assertSame('data11', $result[0]['Header1']);
        $this->assertSame('data12', $result[0]['Header2']);
        $this->assertSame('data13', $result[0]['Header 3']);
        $this->assertSame('Data 14', $result[0]['Header 4']);
        $this->assertSame('mdata 1', $result[0]['Header multidimensional']);

        $this->assertSame('1', $result[1]['Header1']);
        $this->assertSame('2', $result[1]['Header2']);
        $this->assertSame('3', $result[1]['Header 3']);
        $this->assertSame('4', $result[1]['Header 4']);
        $this->assertIsArray($result[1]['Header multidimensional']);
        $this->assertSame(['1.1', '1.2', '1.3'], $result[1]['Header multidimensional']);

        $this->assertSame('Data 1', $result[2]['Header1']);
        $this->assertIsArray($result[2]['Header2']);
        $this->assertSame(['Data 2', 'Data 2.1', 'Data 2.2'], $result[2]['Header2']);
        $this->assertSame('Data 3', $result[2]['Header 3']);
        $this->assertSame('Data 4', $result[2]['Header 4']);
        $this->assertIsArray($result[2]['Header multidimensional']);
        $this->assertSame(['Data 5', 'Data 5.1', 'Data 5.2', 'Data 5.3', 'Data 5.4', 'Data 5.5'], $result[2]['Header multidimensional']);
    }
}
