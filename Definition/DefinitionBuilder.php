<?php

namespace whatwedo\ImportBundle\Definition;

class DefinitionBuilder
{
    /** @var ImportRow[] */
    private array $importRows = [];

    public function addRow(ImportRow $importRow): self
    {
        $this->importRows[$importRow->getAcronym()] = $importRow;
        return $this;
    }

    /**
     * @return ImportRow[]
     */
    public function getConfiguration(): array
    {
        return $this->importRows;
    }
}