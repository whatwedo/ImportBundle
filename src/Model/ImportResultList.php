<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Model;

class ImportResultList
{
    private array $errors = [];

    private array $importedEntities = [];

    /**
     * @var ImportResultItem[]
     */
    private array $resultItems = [];

    public function getErrorCount(): int
    {
        $errors = 0;
        $errors += count($this->errors);
        foreach ($this->resultItems as $item) {
            $errors += count($item->getValidationViolations());
        }

        return $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function getResultItems(): array
    {
        return $this->resultItems;
    }

    public function addItem(ImportResultItem $data): void
    {
        $this->resultItems[] = $data;
    }

    public function getImportedEntities(): array
    {
        return $this->importedEntities;
    }

    public function addImportedEntity($entity): void
    {
        $this->importedEntities[] = $entity;
    }
}
