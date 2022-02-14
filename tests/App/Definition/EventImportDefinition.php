<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests\App\Definition;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use whatwedo\ImportBundle\Definition\AbstractImportDefinition;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Definition\ImportColumn;
use whatwedo\ImportBundle\Importer\CallbackImporter;
use whatwedo\ImportBundle\Importer\DataImporterInterface;
use whatwedo\ImportBundle\Model\ImportResultItem;
use whatwedo\ImportBundle\Prepare\DataAdapterInterface;
use whatwedo\ImportBundle\Prepare\TextDataAdapter;
use whatwedo\ImportBundle\Tests\App\Entity\Department;
use whatwedo\ImportBundle\Tests\App\Entity\Event;

final class EventImportDefinition extends AbstractImportDefinition
{
    private EntityManagerInterface $entityManager;

    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function getEntityClass(): string
    {
        return Event::class;
    }

    public function configureImport(DefinitionBuilder $builder): void
    {
        $builder->addColumn(
            new ImportColumn(
                'name',
                [
                    ImportColumn::OPTION_REQUIRED => true,
                    ImportColumn::OPTION_CONSTRAINTS => [new Assert\NotBlank()],
                    ImportColumn::OPTION_HELP => 'Name of the Event',
                ]
            )
        )
            ->addColumn(
                new ImportColumn(
                    'startDate',
                    [
                        ImportColumn::OPTION_CONVERTER => static fn (string $importData) => \DateTimeImmutable::createFromFormat('d.m.Y H:i', $importData),
                        ImportColumn::OPTION_REQUIRED => true,
                        ImportColumn::OPTION_CONSTRAINTS => [
                            new Assert\NotBlank(),
                            new Assert\DateTime('d.m.Y H:i'),
                        ],
                        ImportColumn::OPTION_HELP => 'Date in format d.m.Y H:i, eg: 21.12.2021 12:00',
                        ImportColumn::OPTION_FORMATTER => static fn (\DateTimeImmutable $date) => $date->format('d.m.Y H:i'),
                    ]
                )
            )
            ->addColumn(
                new ImportColumn(
                    'endDate',
                    [
                        ImportColumn::OPTION_CONVERTER => static fn (string $importData) => \DateTimeImmutable::createFromFormat('d.m.Y H:i', $importData),
                        ImportColumn::OPTION_HELP => 'Date in format d.m.Y H:i, eg: 21.12.2021 13:00',
                        ImportColumn::OPTION_REQUIRED => true,
                        ImportColumn::OPTION_CONSTRAINTS => [
                            new Assert\NotBlank(),
                            new Assert\DateTime('d.m.Y H:i'),
                        ],
                        ImportColumn::OPTION_FORMATTER => static fn (\DateTimeImmutable $date) => $date->format('d.m.Y H:i'),
                    ]
                )
            )
            ->addColumn(
                new ImportColumn(
                    'department',
                    [
                        ImportColumn::OPTION_QUERY_CRITERIA => 'name',
                        ImportColumn::OPTION_PROPERTY_SETTER => static function (Event $event, string $property, ?Department $department) {
                            $event->addDepartment($department);
                        },
                        ImportColumn::OPTION_HELP => 'Name of the Department',
                        ImportColumn::OPTION_FORMATTER => static fn (?Department $department) => $department ? $department->getName() : '',
                        ImportColumn::OPTION_REQUIRED => true,
                        ImportColumn::OPTION_CONSTRAINTS => [
                            new Assert\NotBlank(),
                        ],
                        ImportColumn::OPTION_ALLOWED_VALUES => function () {
                            $result = [];
                            $values = $this->entityManager->getRepository(Department::class)->findAll();
                            foreach ($values as $item) {
                                $result[] = $item->getName();
                            }

                            return $result;
                        },
                    ]
                )
            )
            ->addColumn(
                new ImportColumn(
                    'eventType',
                    [
                        ImportColumn::OPTION_HELP => 'type of the Event',
                        ImportColumn::OPTION_REQUIRED => false,
                        ImportColumn::OPTION_ALLOWED_VALUES => [
                            Event::TYPE_MEETING,
                            Event::TYPE_APOINTMENT,
                        ],
                    ]
                )
            )
        ;
    }

    public function getDataAdapter(): DataAdapterInterface
    {
        return new TextDataAdapter(PHP_EOL, ';', '"');
    }

    public function getDataImporter(): DataImporterInterface
    {
        return new CallbackImporter(fn (array $importRow, DefinitionBuilder $definitionBuilder) => $this->importDataRow($importRow, $definitionBuilder));
    }

    protected function importDataRow(array $importRow, DefinitionBuilder $definitionBuilder): ImportResultItem
    {
        $event = new Event();

        $event->setName($importRow['name']);
        $event->setStartDate(
            $definitionBuilder->getColumnConfiguration('startDate')->getConverter()($importRow['startDate'])
        );
        $event->setEndDate(
            $definitionBuilder->getColumnConfiguration('endDate')->getConverter()($importRow['endDate'])
        );

        $violations = $this->validator->validate($event);

        $importResultItem = new ImportResultItem($event);
        $importResultItem->setValidationViolations($violations);

        return $importResultItem;
    }
}
