<?php

namespace whatwedo\ImportBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use whatwedo\ImportBundle\Definition\ImportDefinitionInterface;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Model\Import;

class ImportManager
{
    private EntityManagerInterface $entityManager;
    private ?PropertyInfoExtractor $propertyInfoExtractor = null;
    private ValidatorInterface $validator;
    /**
     * @var ImportDefinitionInterface[]
     */
    protected $definitions = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    )
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }


    /**
     * @param $alias
     */
    public function addDefinition(ImportDefinitionInterface $definition)
    {
        $this->definitions[$definition->getEntity()] = $definition;
    }


    public function importData(Import $importDto, string $importClass)
    {

        $importResult = [
            'errors' => 0,
            'data' => [],
        ];
        $data = $this->prepareData($importDto);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();




        $definition = $this->getDefinition($importClass);
        $definitionBuilder = $this->getDefinitionBuilder($definition);

        $classProperties =  $this->getPropertyInfoExtractor()->getProperties($importClass);

        foreach ($data as $item) {

            $importRowResult = [];
            $importRowResult['entity'] = [];

            $newImportEntity = $definition->createEntity();
            $importRowResult['entity'] = $newImportEntity;

            foreach ($definitionBuilder->getConfiguration() as $importRow) {

                if (in_array($importRow->getAccessorPath(), $classProperties)) {
                    $accessorPath = $importRow->getAccessorPath();
                    $typeInfo =  $this->getPropertyInfoExtractor()->getTypes($importClass, $accessorPath);

                    if (!isset($item[$importRow->getAcronym()])) {
                        continue;
                    }

                    $importData = $item[$importRow->getAcronym()];
                    if ($importRow->getConverter())  {
                        $importData = $importRow->getConverter()($item[$importRow->getAcronym()]);
                    }

                    if ($typeInfo[0]->getBuiltinType() == 'object') {
                        try {
                            $md = $this->entityManager->getClassMetadata($typeInfo[0]->getClassName());
                            $importEntity = $this->entityManager->getRepository($typeInfo[0]->getClassName())->findOneBy(
                                [ $importRow->getQueryCriteria() => $importData ]
                            );

                            if ($importEntity) {
                                $importData = $importEntity;
                            }
                        } catch (\Doctrine\Persistence\Mapping\MappingException $ex) {
                            $o = 0;
                        }

                    }

                    if ($importRow->getPropertySetter()) {
                        $importRow->getPropertySetter()($newImportEntity, $accessorPath, $importData);
                    } else {
                        $propertyAccessor->setValue($newImportEntity, $accessorPath, $importData);
                    }
                }
            }


            if ($definition->validate()) {
                $importRowResult['validation'] = $this->validator->validate($newImportEntity);
                $importResult['errors'] += count($importRowResult['validation']);

            }

            $definition->prePersit($newImportEntity);

            $this->entityManager->persist($newImportEntity);
            $importResult['data'][] = $importRowResult;
        }


        if (!$importDto->isSimulate()) {
            $this->entityManager->flush();
        }

        return $importResult;

    }



    /**
     * @param Import $importDto
     */
    private function prepareData(Import $importDto): array
    {
        $headers = [];
        $rows = [];


        $lines = explode(PHP_EOL, $importDto->getImportData());

        if (isset($lines[0])) {
            $headers = explode("\t", trim($lines[0]));
        }


        for ($i = 1; $i < count($lines); $i++) {
            $rowItem = [];
            $line = trim($lines[$i]);
            if ($line != '') {
                $line = explode("\t", $line);

                foreach ($headers as $headerIndex => $headerKey) {
                    $rowItem[$headerKey] = trim($line[$headerIndex]);
                }
                $rows[] = $rowItem;
            }
        }

        return $rows;
    }

    /**
     * @return PropertyInfoExtractor
     */
    private function getPropertyInfoExtractor(): PropertyInfoExtractor
    {
        if (!$this->propertyInfoExtractor) {
            $reflectionExtractor = new ReflectionExtractor();
            $doctrineExtractor = new DoctrineExtractor($this->entityManager);

            $this->propertyInfoExtractor = new PropertyInfoExtractor(
            // List extractors
                [
                    $reflectionExtractor,
                    $doctrineExtractor
                ],
                [
                    $reflectionExtractor,
                    $doctrineExtractor
                ]
            );
        }
        return $this->propertyInfoExtractor;
    }

    public function getDefinition(string $class): ImportDefinitionInterface
    {
        if (!isset($this->definitions[$class])) {
            throw new ElementNotFoundException(sprintf('Import Definition with the class "%s" not found.', $class));
        }

        return $this->definitions[$class];
    }

    public function getDefinitionBuilder(ImportDefinitionInterface $definition): DefinitionBuilder
    {
        $definitionBuilder = new DefinitionBuilder();
        $definition->configureImport($definitionBuilder);
        return $definitionBuilder;
    }


}