<?php

namespace whatwedo\ImportBundle\Definition;


interface ImportDefinitionInterface
{
    /**
     * returns the fqdn of the entity
     *
     * @return string fqdn of the entity
     */
    public function getEntity(): string;


    public function createEntity(): object;

    /**
     * builds the interface
     */
    public function configureImport(DefinitionBuilder $builder): void;


    public function validate(): bool;

    public function prePersit(object $newImportEntity);

}