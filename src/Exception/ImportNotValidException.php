<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Exception;

class ImportNotValidException extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('import data is not valid'));
    }
}
