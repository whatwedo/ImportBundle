<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Definition;

use whatwedo\ImportBundle\Exception\ImportColumnNotDefinedException;

class DefinitionBuilder
{
    /**
     * @var ImportColumn[]
     */
    private array $importColumns = [];

    private ImportDefinitionInterface $definition;

    private function __construct()
    {
    }

    public static function create(ImportDefinitionInterface $definition): self
    {
        $builder = new self();
        $definition->configureImport($builder);
        $builder->setDefinition($definition);

        return $builder;
    }

    public function addColumn(ImportColumn $importColumn): self
    {
        $this->importColumns[$importColumn->getAcronym()] = $importColumn;

        return $this;
    }

    /**
     * @return ImportColumn[]
     */
    public function getConfiguration(): array
    {
        return $this->importColumns;
    }

    public function getColumnConfiguration(string $acronym): ImportColumn
    {
        if (isset($this->importColumns[$acronym])) {
            return $this->importColumns[$acronym];
        }

        throw new ImportColumnNotDefinedException($acronym, get_class($this->definition));
    }

    public function createEntity(array $importRow)
    {
        return $this->definition->createEntity($importRow);
    }

    private function setDefinition(ImportDefinitionInterface $definition)
    {
        $this->definition = $definition;
    }
}
