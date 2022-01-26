<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Manager;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Definition\ImportColumn;
use whatwedo\ImportBundle\Model\ValidationResult;

class ImportDataValidator
{
    public const CODE_REQUIRED = 'isRequired';

    public const CODE_NOT_ALLOWED = 'notAllowed';

    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function isValid(array $importData, DefinitionBuilder $definitionBuilder): bool
    {
        return count($this->validateImport($importData, $definitionBuilder)) === 0;
    }

    public function validate(array $dataRow, DefinitionBuilder $definitionBuilder): ValidationResult
    {
        $validationResult = new ValidationResult();
        $validationResult->setDataRow($dataRow);
        $constraintCollection = [];

        foreach ($definitionBuilder->getConfiguration() as $cellConfiguration) {
            $acronym = $cellConfiguration->getAcronym();
            if ($cellConfiguration->isRequired()) {
                if (! isset($dataRow[$acronym])) {
                    $validationResult->add(
                        new ConstraintViolation(
                            sprintf('"%s" is required', $acronym),
                            null,
                            [],
                            null,
                            sprintf('[%s]', $acronym),
                            null,
                            null,
                            self::CODE_REQUIRED
                        )
                    );
                }
            }

            if (isset($dataRow[$acronym]) && $cellConfiguration->hasOption(ImportColumn::OPTION_CONSTRAINTS)) {
                $constraintCollection[$acronym] = $cellConfiguration->getConstraints();
            }

            if (isset($dataRow[$acronym]) && $cellConfiguration->hasOption(ImportColumn::OPTION_ALLOWED_VALUES)) {
                $allowedValue = $cellConfiguration->getAllowedValues();

                $values = $dataRow[$acronym];
                if (! is_array($values)) {
                    $values = [$values];
                }

                foreach ($values as $value) {
                    if (! in_array($value, $allowedValue, true)) {
                        $validationResult->add(
                            new ConstraintViolation(
                                sprintf('value "%s" for "%s" is not allowed', $value, $acronym),
                                null,
                                [],
                                null,
                                sprintf('[%s]', $acronym),
                                null,
                                null,
                                self::CODE_NOT_ALLOWED
                            )
                        );
                    }
                }
            }
        }

        $violations = $this->validator->validate($dataRow, new Collection($constraintCollection));

        foreach ($violations as $violation) {
            $validationResult->add($violation);
        }

        return $validationResult;
    }

    public function validateImport(array $importData, DefinitionBuilder $definitionBuilder): array
    {
        $validationErrors = [];

        foreach ($importData as $rowNumber => $importRow) {
            $validationError = $this->validate($importRow, $definitionBuilder);
            if ($validationError->count() > 0) {
                $validationErrors[$rowNumber + 2] = $validationError;
            }
        }

        return $validationErrors;
    }
}
