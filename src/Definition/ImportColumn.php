<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Definition;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportColumn
{
    public const OPTION_ACCESSOR_PATH = 'accessor_path';

    public const OPTION_CONVERTER = 'converter';

    public const OPTION_QUERY_CRITERIA = 'query_criteria';

    public const OPTION_PROPERTY_SETTER = 'property_setter';

    public const OPTION_HELP = 'help';

    public const OPTION_REQUIRED = 'required';

    public const OPTION_ALLOWED_VALUES = 'allowed_values';

    public const OPTION_FORMATTER = 'formatter';

    public const OPTION_CONSTRAINTS = 'constraints';

    private string $acronym;

    private array $options;

    public function __construct(string $acronym, array $options = [])
    {
        $this->acronym = $acronym;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function getAcronym(): string
    {
        return $this->acronym;
    }

    public function getAccessorPath(): string
    {
        return $this->options[self::OPTION_ACCESSOR_PATH];
    }

    public function getConverter(): ?callable
    {
        return $this->options[self::OPTION_CONVERTER];
    }

    public function getPropertySetter(): ?callable
    {
        return $this->options[self::OPTION_PROPERTY_SETTER];
    }

    public function getFormatter(): ?callable
    {
        return $this->options[self::OPTION_FORMATTER];
    }

    public function format($data): string
    {
        return $this->options[self::OPTION_FORMATTER]($data);
    }

    public function getQueryCriteria(): string
    {
        return $this->options[self::OPTION_QUERY_CRITERIA];
    }

    public function getHelp(): ?string
    {
        return $this->options[self::OPTION_HELP];
    }

    public function isRequired(): bool
    {
        return $this->options[self::OPTION_REQUIRED];
    }

    public function getAllowedValues()
    {
        if (is_callable($this->options[self::OPTION_ALLOWED_VALUES])) {
            return $this->options[self::OPTION_ALLOWED_VALUES]();
        }

        return $this->options[self::OPTION_ALLOWED_VALUES];
    }

    public function getConstraints(): array
    {
        return $this->options[self::OPTION_CONSTRAINTS];
    }

    public function hasOption(string $option): bool
    {
        if (isset($this->options[$option]) && $this->options[$option] !== null) {
            if (is_array($this->options[$option]) && count($this->options[$option]) === 0) {
                return false;
            }

            return true;
        }

        return false;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            self::OPTION_ACCESSOR_PATH => $this->acronym,
            self::OPTION_CONVERTER => null,
            self::OPTION_QUERY_CRITERIA => null,
            self::OPTION_PROPERTY_SETTER => null,
            self::OPTION_HELP => null,
            self::OPTION_REQUIRED => false,
            self::OPTION_ALLOWED_VALUES => null,
            self::OPTION_CONSTRAINTS => [],
            self::OPTION_FORMATTER => fn ($data) => $data,
        ]);

        $resolver->setAllowedTypes(self::OPTION_CONVERTER, ['null', 'callable']);
        $resolver->setAllowedTypes(self::OPTION_PROPERTY_SETTER, ['null', 'callable']);
        $resolver->setAllowedTypes(self::OPTION_QUERY_CRITERIA, ['null', 'string']);
        $resolver->setAllowedTypes(self::OPTION_HELP, ['null', 'string']);
        $resolver->setAllowedTypes(self::OPTION_REQUIRED, ['bool']);
        $resolver->setAllowedTypes(self::OPTION_ALLOWED_VALUES, ['null', 'array', 'callable']);
        $resolver->setAllowedTypes(self::OPTION_CONSTRAINTS, ['array']);
        $resolver->setAllowedTypes(self::OPTION_FORMATTER, ['callable']);
    }
}
