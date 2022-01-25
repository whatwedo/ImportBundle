<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Model;

use Symfony\Component\Validator\ConstraintViolationList;

class ValidationResult extends ConstraintViolationList
{
    private array $dataRow = [];

    public function setDataRow(array $dataRow): void
    {
        $this->dataRow = $dataRow;
    }

    public function getDataRow(): array
    {
        return $this->dataRow;
    }

    public function findAcronym(string $acronym): array
    {
        $violations = [];
        foreach ($this as $violation) {
            if ($violation->getPropertyPath() === $acronym) {
                $violations[] = $violation;
            }
        }

        return $violations;
    }
}
