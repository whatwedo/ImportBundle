<?php

namespace whatwedo\ImportBundle\Definition;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportRow
{
    const OPTION_ACCESSOR_PATH = 'accessor_path';
    const OPTION_CONVERTER = 'converter';
    const OPTION_QUERY_CRITERIA = 'query_criteria';
    const OPTION_PROPERTY_SETTER = 'property_setter';
    const OPTION_HELP = 'help';
    const OPTION_REQUIRED = 'required';
    const OPTION_ALLOWED_VALUES = 'allowed_values';
    const OPTION_FORMATTER = 'formatter';
    private string $acronym;
    private array $options;

    /**
     * @param string $acronym
     */
    public function __construct(string $acronym, array $options = [])
    {
        $this->acronym = $acronym;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * @return string
     */
    public function getAcronym(): string
    {
        return $this->acronym;
    }

    public function getAccessorPath(): string
    {
        return $this->options[self::OPTION_ACCESSOR_PATH];
    }

    public function getConverter(): ?callable {
        return $this->options[self::OPTION_CONVERTER];
    }

    public function getPropertySetter(): ?callable {
        return $this->options[self::OPTION_PROPERTY_SETTER];
    }

    public function getFormatter(): ?callable {
        return $this->options[self::OPTION_FORMATTER];
    }

    public function format($data): string {
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

    public function getRequired(): bool
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
            self::OPTION_FORMATTER => fn ($data) => $data,
        ]);


        $resolver->setAllowedTypes(self::OPTION_CONVERTER, ['null', 'callable']);
        $resolver->setAllowedTypes(self::OPTION_PROPERTY_SETTER, ['null', 'callable']);
        $resolver->setAllowedTypes(self::OPTION_QUERY_CRITERIA, ['null', 'string']);
        $resolver->setAllowedTypes(self::OPTION_HELP, ['null', 'string']);
        $resolver->setAllowedTypes(self::OPTION_REQUIRED, ['bool']);
        $resolver->setAllowedTypes(self::OPTION_ALLOWED_VALUES, ['null', 'array', 'callable']);
        $resolver->setAllowedTypes(self::OPTION_FORMATTER, ['callable']);

    }



}