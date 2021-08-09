<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Definition;

abstract class AbstractImportDefinition implements ImportDefinitionInterface
{
    abstract public function getEntityClass(): string;

    public function createEntity(array $data): object
    {
        $entityClass = $this->getEntityClass();

        return new $entityClass();
    }
}
