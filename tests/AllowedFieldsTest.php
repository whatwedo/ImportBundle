<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Tests\App\Definition\EventImportDefinition;
use whatwedo\ImportBundle\Tests\App\Entity\Department;
use whatwedo\ImportBundle\Tests\App\Entity\Event;
use whatwedo\ImportBundle\Tests\App\Factory\DepartmentFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AllowedFieldsTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testAllowedValueQuery()
    {
        $this->_resetSchema();

        DepartmentFactory::createOne([
            'name' => 'Department 1',
        ]);
        DepartmentFactory::createOne([
            'name' => 'Department 2',
        ]);
        DepartmentFactory::createOne([
            'name' => 'Department 3',
        ]);
        DepartmentFactory::createOne([
            'name' => 'Department 4',
        ]);

        $deps = self::getContainer()->get(EntityManagerInterface::class)->getRepository(Department::class)->findAll();

        $this->assertCount(4, $deps);

        /** @var EventImportDefinition $eventImportDefinition */
        $eventImportDefinition = self::getContainer()->get(EventImportDefinition::class);

        $definitionBuilder = DefinitionBuilder::create($eventImportDefinition);

        $importColumn = $definitionBuilder->getColumnConfiguration('department');

        $allowedDepartments = $importColumn->getAllowedValues();

        $this->assertCount(4, $allowedDepartments);
        $this->assertSame([
            'Department 1',
            'Department 2',
            'Department 3',
            'Department 4',
        ], $allowedDepartments);
    }

    public function testAllowedValuesArray()
    {
        /** @var EventImportDefinition $eventImportDefinition */
        $eventImportDefinition = self::getContainer()->get(EventImportDefinition::class);

        $definitionBuilder = DefinitionBuilder::create($eventImportDefinition);

        $importColumn = $definitionBuilder->getColumnConfiguration('eventType');

        $allowedValues = $importColumn->getAllowedValues();

        $this->assertCount(2, $allowedValues);
        $this->assertSame([
            Event::TYPE_MEETING,
            Event::TYPE_APOINTMENT,
        ], $allowedValues);
    }
}
