<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Importer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Model\ImportResultItem;

class PropertyAccessorImporter implements DataImporterInterface
{
    private ?PropertyInfoExtractor $propertyInfoExtractor = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function import(array $importData, DefinitionBuilder $definitionBuilder): ImportResultItem
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $entity = $definitionBuilder->createEntity($importData);
        $classProperties = $this->getPropertyInfoExtractor()->getProperties($entity::class);

        $importRowResult = new ImportResultItem($entity);

        foreach ($definitionBuilder->getConfiguration() as $importColumn) {
            if (in_array($importColumn->getAccessorPath(), $classProperties, true)) {
                $accessorPath = $importColumn->getAccessorPath();
                $typeInfo = $this->getPropertyInfoExtractor()->getTypes($entity::class, $accessorPath);

                if (! isset($importData[$importColumn->getAcronym()])) {
                    continue;
                }

                $importDataItem = $importData[$importColumn->getAcronym()];
                if ($importColumn->getConverter()) {
                    $importDataItem = $importColumn->getConverter()($importData[$importColumn->getAcronym()]);
                }

                if ($typeInfo[0]->getBuiltinType() === 'object') {
                    try {
                        $md = $this->entityManager->getClassMetadata($typeInfo[0]->getClassName());
                        $importEntity = $this->entityManager->getRepository($typeInfo[0]->getClassName())->findOneBy(
                            [
                                $importColumn->getQueryCriteria() => $importData,
                            ]
                        );

                        if ($importEntity) {
                            $importDataItem = $importEntity;
                        }
                    } catch (\Doctrine\Persistence\Mapping\MappingException $ex) {
                        $o = 0;
                    }
                }

                if ($importColumn->getPropertySetter()) {
                    $importColumn->getPropertySetter()($entity, $accessorPath, $importDataItem);
                } else {
                    $propertyAccessor->setValue($entity, $accessorPath, $importDataItem);
                }
            }
        }

        return $importRowResult;
    }

    private function getPropertyInfoExtractor(): PropertyInfoExtractor
    {
        if (! $this->propertyInfoExtractor) {
            $reflectionExtractor = new ReflectionExtractor();
            $doctrineExtractor = new DoctrineExtractor($this->entityManager);

            $this->propertyInfoExtractor = new PropertyInfoExtractor(
            // List extractors
                [
                    $reflectionExtractor,
                    $doctrineExtractor,
                ],
                [
                    $reflectionExtractor,
                    $doctrineExtractor,
                ]
            );
        }

        return $this->propertyInfoExtractor;
    }
}
