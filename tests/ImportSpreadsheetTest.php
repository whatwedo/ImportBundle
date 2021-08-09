<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Manager\ImportDataValidator;
use whatwedo\ImportBundle\Manager\ImportManager;
use whatwedo\ImportBundle\Model\ImportResultList;
use whatwedo\ImportBundle\Tests\Fixtures\Definition\EventImportDefinition;
use whatwedo\ImportBundle\Tests\Fixtures\Definition\EventImportSpreadSheetDefinition;
use whatwedo\ImportBundle\Tests\Fixtures\Factory\DepartmentFactory;
use whatwedo\ImportBundle\Tests\Helper\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

class ImportSpreadsheetTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

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
        $eventImportDefinition = self::getContainer()->get(EventImportSpreadSheetDefinition::class);

        $dataAdapter = $eventImportDefinition->getDataAdapter();
        $spreadsheetFile = __DIR__ . '/data/spreadSheetEventTest.xlsx';
        $importData = $dataAdapter->prepare($spreadsheetFile);

        /** @var ImportDataValidator $importValidator */
        $importValidator = self::getContainer()->get(ImportDataValidator::class);

        $definitionBuilder = DefinitionBuilder::create($eventImportDefinition);

        $this->assertTrue($importValidator->isValid($importData, $definitionBuilder));

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
}
