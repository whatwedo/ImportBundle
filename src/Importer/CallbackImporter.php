<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Importer;

use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Model\ImportResultItem;

class CallbackImporter implements DataImporterInterface
{
    private \Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function import(array $importData, DefinitionBuilder $definition): ImportResultItem
    {
        return $this->callback->__invoke($importData, $definition);
    }
}
