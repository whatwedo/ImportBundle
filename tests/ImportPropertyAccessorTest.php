<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\ImportBundle\Manager\ImportManager;
use whatwedo\ImportBundle\Model\ImportResultList;
use whatwedo\ImportBundle\Tests\App\Definition\EventImportDefinition;
use whatwedo\ImportBundle\Tests\App\Definition\EventPropertyAccessorImportDefinition;
use whatwedo\ImportBundle\Tests\App\Factory\DepartmentFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ImportPropertyAccessorTest extends KernelTestCase
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
        $eventImportDefinition = self::getContainer()->get(EventPropertyAccessorImportDefinition::class);

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

        $this->assertCount(2, $entityManager->getRepository(App\Entity\Event::class)->findAll());
    }
}
