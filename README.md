


## samplte


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
    /**
     * @Route("/event/import", name="event_import")
     */
    public function import(Request $request, EventImportDefinition $eventImportDefinition, ImportManager $importManager): Response
    {
        $importDto = new Import();

        $form = $this->createForm(
            ImportType::class,
            $importDto
        );

        $form->handleRequest($request);

        $definitionBuilder = DefinitionBuilder::create($eventImportDefinition);
        $view = 'Pages/Calendar/Import/import.html.twig';

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $importManager->importData($importDto, $eventImportDefinition);

            return $this->renderForm($view, [
                'form' => $form,
                'definitionBuilder' => $definitionBuilder,
                'result' => $result,
            ]);
        }

        return $this->renderForm($view, [
            'form' => $form,
            'definitionBuilder' => $definitionBuilder,
        ]);
    }
}

```

```php
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Calendar;
use App\Entity\Event;
use whatwedo\ImportBundle\Definition\AbstractImportDefinition;
use whatwedo\ImportBundle\Definition\DefinitionBuilder;
use whatwedo\ImportBundle\Definition\ImportColumn;
use whatwedo\ImportBundle\Prepare\DataAdapterInterface;
use whatwedo\ImportBundle\Prepare\TextDataAdapter;

final class EventImportDefinition extends AbstractImportDefinition
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityClass(): string
    {
        return Event::class;
    }

    public function configureImport(DefinitionBuilder $builder): void
    {
        $builder->addColumn(
            new ImportColumn(
                'name',
                [
                    ImportColumn::OPTION_REQUIRED => true,
                    ImportColumn::OPTION_HELP => 'Name of the Event',
                ]
            )
        )
            ->addColumn(
                new ImportColumn(
                    'startDate',
                    [
                        ImportColumn::OPTION_CONVERTER => static fn (string $importData) => \DateTimeImmutable::createFromFormat('d.m.Y H:i', $importData),
                        ImportColumn::OPTION_REQUIRED => true,
                        ImportColumn::OPTION_HELP => 'Date in format d.m.Y H:i, eg: 21.12.2021 12:00',
                        ImportColumn::OPTION_FORMATTER => static fn (\DateTimeImmutable $date) => $date->format('d.m.Y H:i'),
                    ]
                )
            )
            ->addColumn(
                new ImportColumn(
                    'endDate',
                    [
                        ImportColumn::OPTION_CONVERTER => static fn (string $importData) => \DateTimeImmutable::createFromFormat('d.m.Y H:i', $importData),
                        ImportColumn::OPTION_HELP => 'Date in format d.m.Y H:i, eg: 21.12.2021 12:00',
                        ImportColumn::OPTION_REQUIRED => true,
                        ImportColumn::OPTION_FORMATTER => static fn (\DateTimeImmutable $date) => $date->format('d.m.Y H:i'),
                    ]
                )
            )
            ->addColumn(
                new ImportColumn(
                    'calendar',
                    [
                        ImportColumn::OPTION_QUERY_CRITERIA => 'name',
                        ImportColumn::OPTION_PROPERTY_SETTER => static function (Event $event, string $property, ?Calendar $calendar) {
                            $event->setCalendar($calendar);
                            foreach ($calendar->getDepartments() as $department) {
                                $event->addDepartment($department);
                            }
                        },
                        ImportColumn::OPTION_HELP => 'Name of the Calendar',
                        ImportColumn::OPTION_FORMATTER => static fn (?Calendar $calendar) => $calendar ? $calendar->getName() : '',
                        ImportColumn::OPTION_REQUIRED => true,
                        ImportColumn::OPTION_ALLOWED_VALUES => function () {
                            $result = [];
                            $values = $this->entityManager->getRepository(Calendar::class)->findAll();
                            foreach ($values as $item) {
                                $result[] = $item->getName();
                            }

                            return $result;
                        },
                    ]
                )
            )
        ;
    }

    public function prePersit(object $newImportEntity)
    {
    }

    public function getDataAdapter(): DataAdapterInterface
    {
        return new TextDataAdapter(PHP_EOL, "\t", '"');
    }
}

```