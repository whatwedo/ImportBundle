<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Definition;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractImportDefinition implements ImportDefinitionInterface
{
    abstract public function getEntityClass(): string;

    public function createEntity(array $data): object
    {
        $entityClass = $this->getEntityClass();

        return new $entityClass();
    }

    public function persistEntity(object $entity, EntityManagerInterface $entityManager)
    {
        if (! $entityManager->contains($entity)) {
            $entityManager->persist($entity);
        }
    }
}
