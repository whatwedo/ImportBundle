<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Prepare;

interface DataAdapterInterface
{
    public function prepare($data): array;
}
