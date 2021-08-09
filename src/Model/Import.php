<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Model;

class Import
{
    private string $importData = '';

    private bool $simulate = false;

    public function getImportData(): string
    {
        return $this->importData;
    }

    public function setImportData(string $importData): void
    {
        $this->importData = $importData;
    }

    public function isSimulate(): bool
    {
        return $this->simulate;
    }

    public function setSimulate(bool $simulate): void
    {
        $this->simulate = $simulate;
    }
}
