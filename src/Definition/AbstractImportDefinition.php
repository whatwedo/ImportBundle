<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Definition;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractImportDefinition implements ImportDefinitionInterface
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    abstract public function getEntityClass(): string;

    public function createEntity(array $data): object
    {
        $entityClass = $this->getEntityClass();

        return new $entityClass();
    }
}
