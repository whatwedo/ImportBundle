<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use whatwedo\ImportBundle\Model\Import;

final class ImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'importData',
                TextareaType::class,
                [
                    'label' => 'label.importData',
                    'required' => true,
                ]
            )
            ->add(
                'simulate',
                CheckboxType::class,
                [
                    'label' => 'label.simulate',
                    'required' => false,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->getEntityClass(),
        ]);
    }

    protected function getEntityClass(): string
    {
        return Import::class;
    }
}
