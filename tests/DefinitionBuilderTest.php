<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Definition\ImportColumn;
use whatwedo\ImportBundle\Exception\ImportColumnNotDefinedException;
use whatwedo\ImportBundle\Tests\App\Definition\EventImportDefinition;

class DefinitionBuilderTest extends KernelTestCase
{
    public function testDefinitionBuilder()
    {
        $eventImportDefinition = self::getContainer()->get(EventImportDefinition::class);

        $definitionBuilder = DefinitionBuilder::create($eventImportDefinition);

        $this->assertInstanceOf(DefinitionBuilder::class, $definitionBuilder);
    }

    public function testDefinitionBuilderGetImportColomn()
    {
        $eventImportDefinition = self::getContainer()->get(EventImportDefinition::class);

        $definitionBuilder = DefinitionBuilder::create($eventImportDefinition);

        $this->assertInstanceOf(ImportColumn::class, $definitionBuilder->getColumnConfiguration('name'));
    }

    public function testDefinitionBuilderGetImportColomnFail()
    {
        $eventImportDefinition = self::getContainer()->get(EventImportDefinition::class);

        $definitionBuilder = DefinitionBuilder::create($eventImportDefinition);

        $this->expectException(ImportColumnNotDefinedException::class);
        $this->expectExceptionMessage('import column with acronym "gugus" not found in definition "whatwedo\ImportBundle\Tests\App\Definition\EventImportDefinition"');
        $this->assertInstanceOf(ImportColumn::class, $definitionBuilder->getColumnConfiguration('gugus'));
    }
}
