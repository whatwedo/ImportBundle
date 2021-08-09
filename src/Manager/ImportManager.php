<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Manager;

use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Definition\ImportDefinitionInterface;
use whatwedo\ImportBundle\Exception\ImportNotValidException;
use whatwedo\ImportBundle\Model\ImportResultList;

class ImportManager
{
    private ImportDataValidator $importDataValidator;

    public function __construct(
        ImportDataValidator $importDataValidator,
    ) {
        $this->importDataValidator = $importDataValidator;
    }

    public function importData(array $importData, ImportDefinitionInterface $definition): ImportResultList
    {
        $definitionBuilder = DefinitionBuilder::create($definition);
        if (! $this->importDataValidator->isValid($importData, $definitionBuilder)) {
            throw new ImportNotValidException();
        }

        $importResultList = new ImportResultList();

        $dataImporter = $definition->getDataImporter();

        foreach ($importData as $importRow) {
            $importResultList->addItem($dataImporter->import($importRow, $definitionBuilder));
        }

        return $importResultList;
    }
}
