<?php

namespace whatwedo\ImportBundle\Definition;

abstract class AbstractImportDefinition implements ImportDefinitionInterface
{

    abstract public function getEntity(): string;

    public function createEntity(): object
    {
        $entityClass = $this->getEntity();
        return new $entityClass();
    }

    public function validate(): bool
    {
        return true;
    }

    public function prePersit(object $newImportEntity)
    {

    }


}