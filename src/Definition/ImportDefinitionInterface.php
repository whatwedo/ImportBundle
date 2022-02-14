<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Definition;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\ImportBundle\Importer\DataImporterInterface;
use whatwedo\ImportBundle\Prepare\DataAdapterInterface;

interface ImportDefinitionInterface
{
    /**
     * returns the fqdn of the entity.
     *
     * @return string fqdn of the entity
     */
    public function getEntityClass(): string;

    public function createEntity(array $data): object;

    /**
     * builds the interface.
     */
    public function configureImport(DefinitionBuilder $builder): void;

    public function getDataAdapter(): DataAdapterInterface;

    public function getDataImporter(): DataImporterInterface;

    public function persistEntity(object $entity, EntityManagerInterface $entityManager);
}
