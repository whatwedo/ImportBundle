<?php

namespace whatwedo\ImportBundle\Model;

class Import
{
    private string $importData = '';
    private bool $simulate = false;

    /**
     * @return string
     */
    public function getImportData(): string
    {
        return $this->importData;
    }

    /**
     * @param string $importData
     */
    public function setImportData(string $importData): void
    {
        $this->importData = $importData;
    }

    /**
     * @return bool
     */
    public function isSimulate(): bool
    {
        return $this->simulate;
    }

    /**
     * @param bool $simulate
     */
    public function setSimulate(bool $simulate): void
    {
        $this->simulate = $simulate;
    }


}