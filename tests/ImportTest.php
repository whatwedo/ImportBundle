<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\ImportBundle\Exception\ImportNotValidException;
use whatwedo\ImportBundle\Manager\ImportManager;
use whatwedo\ImportBundle\Model\ImportResultList;
use whatwedo\ImportBundle\Tests\Fixtures\Definition\EventImportDefinition;
use whatwedo\ImportBundle\Tests\Fixtures\Factory\DepartmentFactory;
use whatwedo\ImportBundle\Tests\Helper\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

class ImportTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testServiceWiring()
    {
        $importManager = self::getContainer()->get(ImportManager::class);
        $this->assertInstanceOf(ImportManager::class, $importManager);
    }

    public function testImport()
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

        /** @var ImportManager $importManager */
        $importManager = self::getContainer()->get(ImportManager::class);

        /** @var EventImportDefinition $eventImportDefinition */
        $eventImportDefinition = self::getContainer()->get(EventImportDefinition::class);

        $importData = [
            [
                'name' => 'new Event 1',
                'startDate' => '21.12.2021 12:00',
                'endDate' => '21.12.2021 13:00',
                'department' => 'Department 1',
            ],
            [
                'name' => 'new Event 2',
                'startDate' => '22.12.2021 12:00',
                'endDate' => '22.12.2021 13:00',
                'department' => 'Department 2',
            ],
        ];

        $importResultList = $importManager->importData($importData, $eventImportDefinition);

        $this->assertInstanceOf(ImportResultList::class, $importResultList);

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        foreach ($importResultList->getResultItems() as $item) {
            if ($item->getValidationViolations()->count() === 0) {
                $entityManager->persist($item->getEntity());
            }
        }

        $entityManager->flush();

        $this->assertCount(2, $entityManager->getRepository(Fixtures\Entity\Event::class)->findAll());
    }

    public function testImportNotValied()
    {
        /** @var ImportManager $importManager */
        $importManager = self::getContainer()->get(ImportManager::class);

        /** @var EventImportDefinition $eventImportDefinition */
        $eventImportDefinition = self::getContainer()->get(EventImportDefinition::class);

        $importData = [
            [
                'name' => 'new Event 1',
                'startDate' => '21.12.2021 12:00',
                'endDate' => '21.12.2021 13:00',
                'department' => 'Department 1',
            ],
            [
                'startDate' => '22.12.2021 12:00',
                'endDate' => '22.12.2021 13:00',
                'department' => 'Department 2',
            ],
        ];

        $this->expectException(ImportNotValidException::class);

        $importManager->importData($importData, $eventImportDefinition);
    }
}
