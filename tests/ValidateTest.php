<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Manager\ImportDataValidator;
use whatwedo\ImportBundle\Tests\Fixtures\Definition\EventImportDefinition;
use whatwedo\ImportBundle\Tests\Fixtures\Factory\DepartmentFactory;
use whatwedo\ImportBundle\Tests\Helper\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

class ValidateTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testImportRequired()
    {
        /** @var ImportDataValidator $importValidator */
        $importValidator = self::getContainer()->get(ImportDataValidator::class);
        $definitionBuilder = $this->getDefinitionBuilder();

        $importData = [
            'startDate' => '21.12.2021 12:00',
            'endDate' => '21.12.2021 13:00',
        ];

        $validationResult = $importValidator->validate($importData, $definitionBuilder);

        $this->assertSame(2, $validationResult->count());
        $this->assertSame(2, $validationResult->findByCodes(ImportDataValidator::CODE_REQUIRED)->count());
        $this->assertSame('"name" is required', $validationResult->findByCodes(ImportDataValidator::CODE_REQUIRED)->get(0)->getMessage());
        $this->assertSame('"department" is required', $validationResult->findByCodes(ImportDataValidator::CODE_REQUIRED)->get(1)->getMessage());

        $importData = [
            'name' => 'new Event 2',
            'startDate' => '22.12.2021 12:00',
            'endDate' => '22.12.2021 13:00',
        ];

        $validationResult = $importValidator->validate($importData, $definitionBuilder);

        $this->assertSame(1, $validationResult->count());
        $this->assertSame(1, $validationResult->findByCodes(ImportDataValidator::CODE_REQUIRED)->count());
    }

    public function testImportValidatorNotBlank()
    {
        /** @var ImportDataValidator $importValidator */
        $importValidator = self::getContainer()->get(ImportDataValidator::class);

        $definitionBuilder = $this->getDefinitionBuilder();

        $importData = [
            'name' => '',
            'startDate' => '21.12.2021 12:00',
            'endDate' => '21.12.2021 13:00',
            'department' => 'test',
        ];

        $validationResult = $importValidator->validate($importData, $definitionBuilder);

        $this->assertSame(1, $validationResult->findByCodes(NotBlank::IS_BLANK_ERROR)->count());
        $this->assertSame('This value should not be blank.', $validationResult->get(0)->getMessage());
    }

    public function testImportValidatorDate()
    {
        /** @var ImportDataValidator $importValidator */
        $importValidator = self::getContainer()->get(ImportDataValidator::class);

        $definitionBuilder = $this->getDefinitionBuilder();

        $importData = [
            'name' => 'test Date',
            'startDate' => '21.13.2021 25:00',
            'endDate' => '21.12.2021 13:00',
            'department' => 'test',
        ];

        $validationResult = $importValidator->validate($importData, $definitionBuilder);

        $this->assertSame(1, $validationResult->findByCodes(DateTime::INVALID_DATE_ERROR)->count());
        $this->assertSame('This value is not a valid datetime.', $validationResult->get(0)->getMessage());
    }

    public function testImportValidatorAllowedValue()
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

        /** @var ImportDataValidator $importValidator */
        $importValidator = self::getContainer()->get(ImportDataValidator::class);

        $definitionBuilder = $this->getDefinitionBuilder();

        $importData = [
            'name' => 'test Date',
            'startDate' => '21.12.2021 12:00',
            'endDate' => '21.12.2021 13:00',
            'department' => 'test',
        ];

        $validationResult = $importValidator->validate($importData, $definitionBuilder);

        $this->assertSame(1, $validationResult->findByCodes(ImportDataValidator::CODE_NOT_ALLOWED)->count());
        $this->assertSame(ImportDataValidator::CODE_NOT_ALLOWED, $validationResult->get(0)->getCode());
        $this->assertSame('value "test" for "department" is not allowed', $validationResult->get(0)->getMessage());

        $importData = [
            'name' => 'test Date',
            'startDate' => '21.12.2021 12:00',
            'endDate' => '21.12.2021 13:00',
            'department' => 'Department 1',
        ];

        $validationResult = $importValidator->validate($importData, $definitionBuilder);
        $this->assertSame(0, $validationResult->findByCodes(ImportDataValidator::CODE_NOT_ALLOWED)->count());
    }

    protected function getDefinitionBuilder(): DefinitionBuilder
    {
        /** @var EventImportDefinition $eventImportDefinition */
        $eventImportDefinition = self::getContainer()->get(EventImportDefinition::class);

        return DefinitionBuilder::create($eventImportDefinition);
    }
}
