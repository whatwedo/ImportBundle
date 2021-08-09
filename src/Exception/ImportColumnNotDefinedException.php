<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Exception;

class ImportColumnNotDefinedException extends \Exception
{
    public function __construct(string $acroonym, string $definitionClass)
    {
        parent::__construct(sprintf('import column with acronym "%s" not found in definition "%s"', $acroonym, $definitionClass));
    }
}
