<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Manager;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Definition\ImportColumn;
use whatwedo\ImportBundle\Model\ValidationResult;

class ImportDataValidator
{
    public const CODE_REQUIRED = 'isRequired';

    public const CODE_NOT_ALLOWED = 'notAllowed';

    public const CODE_NOT_MULTIDIMENSIONAL = 'multiDimensionalNotAllowed';

    private ValidatorInterface $validator;

    private TranslatorInterface $translator;

    public function __construct(
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->validator = $validator;
        $this->translator = $translator;
    }

    public function isValid(array $importData, DefinitionBuilder $definitionBuilder): bool
    {
        return count($this->validateImport($importData, $definitionBuilder)) === 0;
    }

    public function validate(array $dataRow, DefinitionBuilder $definitionBuilder): ValidationResult
    {
        $validationResult = new ValidationResult();
        $validationResult->setDataRow($dataRow);
        foreach ($definitionBuilder->getConfiguration() as $cellConfiguration) {
            $acronym = $cellConfiguration->getAcronym();
            if ($cellConfiguration->isRequired()) {
                if (! isset($dataRow[$acronym])) {
                    $validationResult->add(
                        new ConstraintViolation(
                            $this->translator->trans('value.required', [
                                '%acronym%' => $acronym,
                            ], 'import_bundle'),
                            null,
                            [],
                            null,
                            $acronym,
                            null,
                            null,
                            self::CODE_REQUIRED
                        )
                    );
                }
            }

            // check if multidimensional is allowed
            if (isset($dataRow[$acronym]) && is_array($dataRow[$acronym]) && ! $cellConfiguration->isMultidimensional()) {
                $validationResult->add(
                    new ConstraintViolation(
                        $this->translator->trans('value.multidimensional_not_allowed', [
                            '%acronym%' => $acronym,
                        ], 'import_bundle'),
                        null,
                        [],
                        null,
                        $acronym,
                        null,
                        null,
                        self::CODE_NOT_MULTIDIMENSIONAL
                    )
                );
            }
            if (isset($dataRow[$acronym]) && $cellConfiguration->hasOption(ImportColumn::OPTION_CONSTRAINTS)) {
                $violations = $this->validator->validate($dataRow[$acronym], $cellConfiguration->getConstraints());

                /** @var ConstraintViolationInterface $violation */
                foreach ($violations as $violation) {
                    $validationResult->add($violation);
                }
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
                                $this->translator->trans('value.not_allowed', [
                                    '%value%' => $value,
                                    '%acronym%' => $acronym,
                                ], 'import_bundle'),
                                null,
                                [],
                                null,
                                $acronym,
                                null,
                                null,
                                self::CODE_NOT_ALLOWED
                            )
                        );
                    }
                }
            }
        }

        return $validationResult;
    }

    public function validateImport(array $importData, DefinitionBuilder $definitionBuilder): array
    {
        $validationErrors = [];

        foreach ($importData as $importRow) {
            $validationError = $this->validate($importRow, $definitionBuilder);
            if ($validationError->count() > 0) {
                $validationErrors[] = $validationError;
            }
        }

        return $validationErrors;
    }
}
