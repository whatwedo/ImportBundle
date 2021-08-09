<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Model;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ImportResultItem
{
    private ?object $entity = null;

    private ConstraintViolationListInterface $validationViolations;

    public function __construct($entity)
    {
        $this->entity = $entity;
        $this->validationViolations = new ConstraintViolationList();
    }

    public function getEntity(): ?object
    {
        return $this->entity;
    }

    public function getValidationViolations(): ConstraintViolationListInterface
    {
        return $this->validationViolations;
    }

    public function setValidationViolations(ConstraintViolationListInterface $validationViolations): void
    {
        $this->validationViolations = $validationViolations;
    }

    public function addError(string $error): void
    {
        $this->validationViolations[] = $error;
    }
}
