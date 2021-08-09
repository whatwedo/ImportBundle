<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Importer;

use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Model\ImportResultItem;

interface DataImporterInterface
{
    public function import(array $importData, DefinitionBuilder $definition): ImportResultItem;
}
