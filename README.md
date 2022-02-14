# Import-Bundle

Bundle is under development!!





## Sample Code


```php
use App\Definition\Import\EventImportDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Form\ImportType;
use whatwedo\ImportBundle\Manager\ImportManager;
use whatwedo\ImportBundle\Model\Import;

final class EventImportController extends AbstractController
{
       #[Route('/student/import', name: 'student_import', methods: ['GET', 'POST'])]
    public function import(
        Request $request,
        EntityManagerInterface $entityManager,
        StudentImportDefinition $studentImportDefinition,
        ImportManager $importManager,
        ImportDataValidator $importDataValidator
    ): Response {
        $requestData = [];

        $form = $this->createFormBuilder($requestData)
            ->add('importFile', FileType::class, [
                'label' => 'Import File (xls)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => true,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                    ]),
                ],
            ])
            ->getForm();
        $form->handleRequest($request);

        $definitionBuilder = DefinitionBuilder::create($studentImportDefinition);

        if ($form->isSubmitted() && $form->isValid()) {
            // convert data to Array

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('importFile')->getData();

            $data = $studentImportDefinition->getDataAdapter()->prepare($uploadedFile->getRealPath());

            // validate Array Data, required, constraints.....
            $validationErrors = $importDataValidator->validateImport($data, $definitionBuilder);

            if (count($validationErrors)) {
                return $this->renderForm('import/import.html.twig', [
                    'validationErrors' => $validationErrors,
                    'definitionBuilder' => $definitionBuilder,
                    'requestData' => $requestData,
                    'form' => $form,
                ]);
            }

            // oll ok

            // Import Data to Entities
            $importResultList = $importManager->importData($data, $studentImportDefinition);

            $entityValidatorErrors = [];
            foreach ($importResultList->getResultItems() as $item) {
                if ($item->getValidationViolations()->count() === 0) {
                    $entityManager->persist($item->getEntity());
                } else {
                    $entityValidatorErrors[] = $item->getValidationViolations();
                }
            }

            if (count($entityValidatorErrors)) {
                return $this->renderForm('import/import.html.twig', [
                    'entityValidatorErrors' => $entityValidatorErrors,
                    'validationErrors' => $validationErrors,
                    'definitionBuilder' => $definitionBuilder,
                    'requestData' => $requestData,
                    'form' => $form,
                ]);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('import/import.html.twig', [
            'definitionBuilder' => $definitionBuilder,
            'requestData' => $requestData,
            'form' => $form,
        ]);
    }
}

```

```php
use App\Entity\Email;
use App\Entity\Person;
use App\Entity\Phone;
use App\Entity\Student;
use App\Enum\ContactTypeEnum;
use App\Enum\PreferredLanguageEnum;
use App\Enum\TitleEnum;
use App\Validator\ValidAHV;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use whatwedo\ImportBundle\Definition\AbstractImportDefinition;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Definition\ImportColumn;
use whatwedo\ImportBundle\Importer\CallbackImporter;
use whatwedo\ImportBundle\Importer\DataImporterInterface;
use whatwedo\ImportBundle\Model\ImportResultItem;
use whatwedo\ImportBundle\Prepare\DataAdapterInterface;
use whatwedo\ImportBundle\Prepare\PhpSpreadSheetDataAdapter;

class StudentImportDefinition extends AbstractImportDefinition
{
    public const DOB_FORMAT = 'j.n.Y';

    protected ValidatorInterface $validator;

    protected EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
    }

    public function getEntityClass(): string
    {
        return Student::class;
    }

    public function configureImport(DefinitionBuilder $builder): void
    {
        $builder
            ->addColumn(new ImportColumn('Matrikelnummer', [
                ImportColumn::OPTION_REQUIRED => true,
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\NotBlank(),
                ],
                ImportColumn::OPTION_HELP => 'Matrikelnummer',
            ]))
            ->addColumn(new ImportColumn('Anrede', [
                ImportColumn::OPTION_HELP => 'Anrede des Studenten',
                ImportColumn::OPTION_REQUIRED => true,
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\NotBlank(),
                ],
                //                ImportColumn::OPTION_ALLOWED_VALUES => function () {
                //                    $result = [];
                //                    $values = $this->entityManager->getRepository(Department::class)->findAll();
                //                    foreach ($values as $item) {
                //                        $result[] = $item->getName();
                //                    }
                //
                //                    return $result;
                //                },
            ]))
            ->addColumn(new ImportColumn('Vorname', [
                ImportColumn::OPTION_REQUIRED => true,
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\NotBlank(),
                ],
            ]))
            ->addColumn(new ImportColumn('ZweiterVorname', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                ],
            ]))
            ->addColumn(new ImportColumn('Nachname', [
                ImportColumn::OPTION_REQUIRED => true,
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\NotBlank(),
                ],
            ]))
            ->addColumn(new ImportColumn('Geburtstag', [
                ImportColumn::OPTION_REQUIRED => true,
                ImportColumn::OPTION_HELP => 'Geburtsdatum im Format j.n.Y Tag und Monat ohen führendes 0',
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\DateTime(self::DOB_FORMAT),
                ],
            ]))
            ->addColumn(new ImportColumn('AHV', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                    new ValidAHV(),
                ],
            ]))
            ->addColumn(new ImportColumn('Heimatort', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                ],
            ]))
            ->addColumn(new ImportColumn('Nationalität', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Country(null, null, true),
                ],
            ]))
            ->addColumn(new ImportColumn('Emailadresse_Privat', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Email(),
                ],
            ]))
            ->addColumn(new ImportColumn('Emailadresse_Student', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Email(),
                ],
            ]))
            ->addColumn(new ImportColumn('Telefon_Privat', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                ],
            ]))
            ->addColumn(new ImportColumn('Telefon_Mobil', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                ],
            ]))
            ->addColumn(new ImportColumn('Adresse', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                ],
            ]))
            ->addColumn(new ImportColumn('PLZ', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                ],
            ]))
            ->addColumn(new ImportColumn('Ort', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                ],
            ]))
            ->addColumn(new ImportColumn('Korrespondenzsprache', [
                ImportColumn::OPTION_CONSTRAINTS => [
                    new Assert\Optional(),
                ],
            ]))
            ;
    }

    public function getDataAdapter(): DataAdapterInterface
    {
        return new PhpSpreadSheetDataAdapter();
    }

    public function getDataImporter(): DataImporterInterface
    {
        return new CallbackImporter(fn (array $importRow, DefinitionBuilder $definitionBuilder) => $this->importDataRow($importRow, $definitionBuilder));
    }

    protected function importDataRow(array $importRow, DefinitionBuilder $definitionBuilder): ImportResultItem
    {
        $person = new Person();

//        Anrede

        $person->setSalutation(TitleEnum::DR);

        $person->setFirstname($importRow['Vorname']);
        if (isset($importRow['ZweiterVorname'])) {
            $person->setMiddlename($importRow['ZweiterVorname']);
        }
        $person->setLastname($importRow['Nachname']);

        $person->setDateOfBirth(\DateTimeImmutable::createFromFormat(self::DOB_FORMAT, $importRow['Geburtstag']));

        $person->setHometown($importRow['Heimatort']);
        $person->setNationality($importRow['Nationalität']);
        $person->setAhvNumber($importRow['AHV']);
        $person->setStreet($importRow['Adresse']);
        $person->setCity($importRow['Ort']);
        $person->setZip($importRow['PLZ']);

        //        Korrespondenzsprache
        $person->setPreferredLanguage(PreferredLanguageEnum::DE);

        $student = new Student($person);
        $student->setMatriculationNumber($importRow['Matrikelnummer']);

        $email = new Email($person);
        $email->setValue($importRow['Emailadresse_Privat']);
        $email->setType(ContactTypeEnum::PRIVATE);

        $email = new Email($person);
        $email->setValue($importRow['Emailadresse_Student']);
        $email->setType(ContactTypeEnum::STUDENT);

        $phone = new Phone($person);
        $phone->setValue($importRow['Telefon_Privat']);
        $phone->setType(ContactTypeEnum::PRIVATE);

        $phone = new Phone($person);
        $phone->setValue($importRow['Telefon_Mobil']);
        $phone->setType(ContactTypeEnum::BUSINESS);

        $violations = $this->validator->validate($student);

        $importResultItem = new ImportResultItem($student);
        $importResultItem->setValidationViolations($violations);

        return $importResultItem;
    }
}
```