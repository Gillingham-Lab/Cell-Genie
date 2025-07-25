<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToggleColumn;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Tool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\FormRowTypeEnum;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Experiment\ExperimentalRunConditionRepository;
use App\Service\Experiment\ExperimentalDataService;
use App\Twig\Components\EntityReference;
use App\Twig\Components\Experiment\Datum;
use App\Twig\Components\Live\Experiment\ExperimentalDesignForm;
use App\Twig\Components\Live\Experiment\ExperimentalRunDataForm;
use App\Twig\Components\Live\Experiment\ExperimentalRunForm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExperimentController extends AbstractController
{
    #[Route('/experiment', name: 'app_experiments')]
    public function index(): Response
    {
        return $this->render('parts/experiments/design_list.html.twig', []);
    }

    #[Route("/experiment/design/view/{design}", name: "app_experiments_view")]
    #[IsGranted("view", "design")]
    public function viewDesign(
        ExperimentalDesign $design,
    ): Response {
        return $this->render("parts/experiments/design_view.html.twig", [
            "toolbox" => new Toolbox([
                new Tool(
                    path: $this->generateUrl("app_experiments"),
                    icon: "up",
                    buttonClass: "btn-secondary",
                    tooltip: "Experiment overview",
                ),
                new ViewTool(
                    path: $this->generateUrl("app_experiments_view_data", ["design" => $design->getId()]),
                    icon: "data",
                    tooltip: "View experiment data",
                    iconStack: "view",
                ),
                new ViewTool(
                    path: $this->generateUrl("app_api_experiments_view_data", ["design" => $design->getId()]),
                    icon: "data",
                    buttonClass: "btn-secondary",
                    tooltip: "Download data",
                    iconStack: "download",
                ),
                new EditTool(
                    path: $this->generateUrl("app_experiments_edit", ["design" => $design->getId()]),
                    enabled: $this->isGranted("edit", $design),
                ),
                new AddTool(
                    path: $this->generateUrl("app_experiments_run_new", ["design" => $design->getId()]),
                    icon: "experiment",
                    iconStack: "add",
                    enabled: $this->isGranted("edit", $design),
                ),
            ]),
            "design" => $design,
            "displayData" => false,
        ]);
    }

    #[Route("/experiment/design/viewData/{design}", name: "app_experiments_view_data")]
    #[IsGranted("view", "design")]
    public function viewDesignData(
        ExperimentalDesign $design,
    ): Response {
        return $this->render("parts/experiments/design_view.html.twig", [
            "toolbox" => new Toolbox([
                new Tool(
                    path: $this->generateUrl("app_experiments"),
                    icon: "up",
                    buttonClass: "btn-secondary",
                    tooltip: "Experiment overview",
                ),
                new ViewTool(
                    path: $this->generateUrl("app_experiments_view", ["design" => $design->getId()]),
                    tooltip: "View experiment details",
                ),
                new EditTool(
                    path: $this->generateUrl("app_experiments_edit", ["design" => $design->getId()]),
                    enabled: $this->isGranted("edit", $design),
                ),
                new AddTool(
                    path: $this->generateUrl("app_experiments_run_new", ["design" => $design->getId()]),
                    icon: "experiment",
                    iconStack: "add",
                    enabled: $this->isGranted("edit", $design),
                ),
            ]),
            "design" => $design,
            "displayData" => true,
        ]);
    }


    /**
     * @param ExperimentalDataService $dataService
     * @param ExperimentalDesign $design
     * @param int $limit
     * @param int $page
     * @param bool $onlyExposed
     * @param array<string, mixed> $searchQuery
     * @param bool $entitiesAsId
     * @param bool $hideComments
     * @return Response
     * @throws Exception
     */
    #[Route('/api/public/experiment/design/viewData/{design}', name: "app_api_experiments_view_data")]
    ##[IsGranted("view", "design")]
    public function downloadDesignData(
        ExperimentalDataService $dataService,
        ExperimentalDesign $design,
        #[MapQueryParameter]
        int $limit = 100,
        #[MapQueryParameter]
        int $page = 0,
        #[MapQueryParameter]
        bool $onlyExposed = true,
        #[MapQueryParameter]
        array $searchQuery = [],
        #[MapQueryParameter]
        bool $entitiesAsId = false,
        #[MapQueryParameter]
        bool $hideComments = false,
    ): Response {
        $response = new Response(null, Response::HTTP_OK);
        $response->headers->set("Content-Type", "text/plain");

        /** @var ExperimentalDesignField[] $conditionFields */
        $conditionFields = $dataService
            ->getFields($design, $onlyExposed)
            ->filter(fn(ExperimentalDesignField $field) => in_array($field->getRole(), [ExperimentalFieldRole::Top, ExperimentalFieldRole::Condition]))
        ;

        $dataRows = $dataService->getPaginatedResults(searchFields: $searchQuery, design: $design, page: $page, limit: $limit);

        $columns = [
            new Column("Run", fn($x) => $x["run"]->getId()),
            new Column("Condition", fn($x) => $x["set"]->getId()),
            new Column("Scientist", fn($x) => $entitiesAsId ? $x["run"]?->getScientist()?->getId() : $x["run"]?->getScientist()?->getFullName()),
            new Column("Created", fn($x) => $x["run"]->getCreatedAt()?->format("Y-m-d H:i:s")),
            new Column("Modified", fn($x) => $x["run"]->getModifiedAt()?->format("Y-m-d H:i:s")),
        ];

        foreach ($conditionFields as $field) {
            $formRow = $field->getFormRow();

            $columns[] = new Column($field->getLabel(), function ($x) use ($formRow, $dataService, $entitiesAsId) {
                if (!array_key_exists($formRow->getFieldName(), $x)) {
                    return "NAN";
                }

                $value = $x[$formRow->getFieldName()];

                if ($formRow->getType() === FormRowTypeEnum::FloatType) {
                    if (is_array($value)) {
                        $value = array_map(fn($x) => $dataService->convertFloatToString($x, $formRow), $value);
                    } else {
                        $value = $dataService->convertFloatToString($value, $formRow);
                    }
                } elseif ($formRow->getType() === FormRowTypeEnum::EntityType and $entitiesAsId === true) {
                    return method_exists($value, "getId") ? $value->getId() : $value->getUlid();
                } elseif ($formRow->getType() === FormRowTypeEnum::ModelParameterType) {
                    if (is_array($value)) {
                        return $dataService->convertFloatToString($value[1], $formRow);
                    } else {
                        return $dataService->convertFloatToString($value, $formRow);
                    }
                }

                return $value;
            });

            if ($field->getFormRow()->getType() === FormRowTypeEnum::EntityType and $entitiesAsId === false) {
                $config = $field->getFormRow()->getConfiguration();

                if (!array_key_exists("entityType", $config)) {
                    continue;
                }

                if ($config["entityType"] !== "App\Entity\DoctrineEntity\Substance\Chemical") {
                    continue;
                }

                $columns[] = new Column($field->getLabel() . " (SMILES)", function ($x) use ($formRow) {
                    if (!array_key_exists($formRow->getFieldName(), $x)) {
                        return "NAN";
                    }

                    /** @var Chemical $value */
                    $value = $x[$formRow->getFieldName()];

                    return $value->getSmiles();
                });
            }
        }

        $table = new Table(
            data: $dataRows,
            columns: $columns,
            maxRows: $dataService->getPaginatedResultCount(searchFields: $searchQuery, design: $design),
        );

        $array = $table->toArray();

        if ($hideComments) {
            $content = "";
        } else {
            $content = "#TotalNumberOfRows\t{$array['maxNumberOfRows']}\n";
        }

        $content .= implode("\t", array_map(
            callback: fn($column) => $column["label"],
            array: $array["columns"],
        )) . "\n";

        foreach ($array["rows"] as $row) {
            $content .= implode("\t", array_map(fn($cell) => $cell["value"], $row)) . "\n";
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * @param ExperimentalDataService $dataService
     * @param ExperimentalRun $run
     * @param int $limit
     * @param int $page
     * @param array<string, mixed> $searchQuery
     * @param bool $entitiesAsId
     * @return Response
     * @throws Exception
     */
    #[Route('/api/public/experiment/run/viewData/{run}', name: "app_api_experiments_run_view_data")]
    ##[IsGranted("view", "run")]
    public function downloadConditionData(
        ExperimentalDataService $dataService,
        ExperimentalRun $run,
        #[MapQueryParameter]
        int $limit = 100,
        #[MapQueryParameter]
        int $page = 0,
        #[MapQueryParameter]
        array $searchQuery = [],
        #[MapQueryParameter]
        bool $entitiesAsId = false,
    ): Response {
        $response = new Response(null, Response::HTTP_OK);
        $response->headers->set("Content-Type", "text/plain");

        $design = $run->getDesign();

        /** @var ExperimentalDesignField[] $conditionFields */
        $conditionFields = $dataService
            ->getFields($design, false)
            ->filter(fn(ExperimentalDesignField $field) => in_array($field->getRole(), [ExperimentalFieldRole::Datum]))
        ;

        $searchQuery = array_merge(
            ["run" => $run],
            $searchQuery,
        );
        $dataRows = $dataService->getPaginatedResults(searchFields: $searchQuery, design: $design, page: $page, limit: $limit);

        // "Up end" data array
        $newDataRows = [];
        foreach ($dataRows as $dataRow) {
            foreach ($dataRow["data"] as $dataSubRow) {
                $newRow = array_merge($dataRow, $dataSubRow);

                $newDataRows[] = $newRow;
            }
        }

        $columns = [
            new Column("Run", fn($x) => $x["run"]->getId()),
            new Column("Condition", fn($x) => $x["set"]->getId()),
        ];

        foreach ($conditionFields as $field) {
            $formRow = $field->getFormRow();

            $columns[] = new Column($field->getLabel(), function ($x) use ($field, $formRow, $dataService, $entitiesAsId) {

                if ($field->getRole() === ExperimentalFieldRole::Comparison) {
                    if (!array_key_exists($formRow->getFieldName(), $x["data"])) {
                        return "NAN";
                    }

                    $value = $x["data"][$formRow->getFieldName()];
                } else {
                    if (!array_key_exists($formRow->getFieldName(), $x)) {
                        return "NAN";
                    }

                    $value = $x[$formRow->getFieldName()];
                }

                if ($formRow->getType() === FormRowTypeEnum::FloatType) {
                    if (is_array($value)) {
                        $value = array_map(fn($x) => $dataService->convertFloatToString($x, $formRow), $value);
                    } else {
                        $value = $dataService->convertFloatToString($value, $formRow);
                    }
                } elseif ($formRow->getType() === FormRowTypeEnum::EntityType and $entitiesAsId === true) {
                    return method_exists($value, "getId") ? $value->getId() : $value->getUlid();
                }

                return $value;
            });
        }

        $table = new Table(
            data: $newDataRows,
            columns: $columns,
            maxRows: $dataService->getPaginatedResultCount(searchFields: $searchQuery, design: $design),
        );

        $array = $table->toArray();

        $content = implode("\t", array_map(fn($column) => $column["label"], $array["columns"])) . "\n";

        foreach ($array["rows"] as $row) {
            $content .= implode("\t", array_map(fn($cell) => $cell["value"], $row)) . "\n";
        }

        $response->setContent($content);

        return $response;
    }

    #[Route("/experiment/design/new", name: 'app_experiments_new')]
    #[IsGranted("ROLE_USER")]
    public function newExperiment(
        #[CurrentUser]
        User $user,
    ): Response {
        return $this->render("parts/forms/component_form.html.twig", [
            "returnTo" => $this->generateUrl("app_experiments"),
            "title" => "Create Experimental Design",
            "subtitle" => "",
            "formComponent" => ExperimentalDesignForm::class,
            "formEntity" => (new ExperimentalDesign())
                ->setOwner($user)
                ->setGroup($user->getGroup())
                ->setPrivacyLevel(PrivacyLevel::Group),
        ]);
    }

    #[Route("/experiment/design/edit/{design}", name: "app_experiments_edit")]
    #[IsGranted("edit", "design")]
    public function editExperiment(
        ExperimentalDesign $design,
    ): Response {
        return $this->render("parts/forms/component_form.html.twig", [
            "returnTo" => $this->generateUrl("app_experiments"),
            "title" => "Edit Experimental Design",
            "subtitle" => "{$design->getNumber()} | {$design->getShortName()}",
            "formComponent" => ExperimentalDesignForm::class,
            "formEntity" => $design,
        ]);
    }

    #[Route("/experiment/design/newRun/{design}", name: "app_experiments_run_new")]
    #[IsGranted("edit", "design")]
    public function newExperimentalRun(
        #[CurrentUser]
        User $user,
        ExperimentalDesign $design,
    ): Response {
        return $this->newOrEditExperimentalRun(
            run: (new ExperimentalRun())
                ->setOwner($user)
                ->setGroup($user->getGroup())
                ->setPrivacyLevel(PrivacyLevel::Group)
                ->setScientist($user)
                ->setDesign($design),
            design: $design,
            title: "Add experimental run",
        );
    }

    #[Route("/experiment/design/cloneRun/{run}", name: "app_experiments_run_clone")]
    #[IsGranted("ROLE_USER")]
    public function cloneExperimentalRun(
        EntityManagerInterface $entityManager,
        ExperimentalRun $run,
    ): Response {
        # Clone the run
        $newRun = clone $run;

        $entityManager->persist($newRun);
        $entityManager->flush();
        return $this->redirectToRoute("app_experiments_run_edit", ["run" => $newRun->getId()->toRfc4122()]);
    }

    #[Route("/experiment/design/editRun/{run}", name: "app_experiments_run_edit")]
    #[IsGranted("edit", "run")]
    public function editExperimentalRun(
        ExperimentalRun $run,
    ): Response {
        return $this->newOrEditExperimentalRun(
            run: $run,
            design: $run->getDesign(),
            title: "Edit experimental run",
            onSubmitRedirectTo: $this->generateUrl("app_experiments_view", ["design" => $run->getDesign()->getId()]),
        );
    }

    private function newOrEditExperimentalRun(
        ExperimentalRun $run,
        ExperimentalDesign $design,
        string $title,
        ?string $onSubmitRedirectTo = null,
    ): Response {
        $tools = [
            new Tool(
                path: $this->generateUrl("app_experiments_view", ["design" => $design->getId()]),
                icon: "up",
                buttonClass: "btn-secondary",
                tooltip: "Return to the run overview",
            ),
        ];

        if ($run->getId()) {
            $tools[] = new Tool(
                path: $this->generateUrl("app_experiments_run_view", ["run" => $run->getId()->toRfc4122()]),
                icon: "left",
                buttonClass: "btn-secondary",
                tooltip: "Return to the run",
            );
        }

        return $this->render("parts/forms/component_form.html.twig", [
            "toolbox" => new Toolbox($tools),
            "onSubmitRedirectTo" => $onSubmitRedirectTo,
            "title" => $title,
            "subtitle" => "{$design->getNumber()} | {$design->getShortName()}",
            "formComponent" => ExperimentalRunForm::class,
            "formEntity" => $run,
            "formComponentData" => [
                "design" => $design,
            ],
        ]);
    }

    #[Route("/experiment/design/addDataToRun/{run}", "app_experiments_run_addData")]
    #[IsGranted("edit", "run")]
    public function addDataToRun(
        ExperimentalRun $run,
    ): Response {
        return $this->render("parts/forms/component_form.html.twig", [
            "toolbox" => new Toolbox([
                new Tool(
                    path: $this->generateUrl("app_experiments_view", ["design" => $run->getDesign()->getId()]),
                    icon: "up",
                    buttonClass: "btn-secondary",
                    tooltip: "Return to the run overview",
                ),
                new Tool(
                    path: $this->generateUrl("app_experiments_run_view", ["run" => $run->getId()]),
                    icon: "left",
                    buttonClass: "btn-secondary",
                    tooltip: "Return to the run",
                ),
            ]),
            "title" => "Add data",
            "subtitle" => "{$run->getDesign()} - {$run->getName()}",
            "no_structure" => true,
            "formComponent" => ExperimentalRunDataForm::class,
            "formEntity" => $run,
            "formComponentData" => [
                "run" => $run,
                "design" => $run->getDesign(),
            ],
        ]);
    }

    #[Route("/experiment/run/{run}", name: "app_experiments_run_view", methods: ["GET"])]
    #[IsGranted("view", "run")]
    public function viewRun(
        Request $request,
        ExperimentalRunConditionRepository $conditionRepository,
        ExperimentalDataService $dataService,
        ExperimentalRun $run,
    ): Response {
        $entitiesToFetch = $dataService->getListOfEntitiesToFetch($run->getConditions(), $run->getDesign(), true);
        $entities = $dataService->fetchEntitiesFromList($entitiesToFetch);

        $getComponentColumn = function (ExperimentalDesignField $field, array $entities) {
            return new ComponentColumn($field->getLabel(), function (ExperimentalRunCondition|ExperimentalRunDataSet $condition) use ($field, $entities) {
                $datum = $condition->getData()[$field->getFormRow()->getFieldName()];
                $value = $datum?->getValue();

                if ($datum?->getType() === DatumEnum::EntityReference) {
                    try {
                        $value = $entities[$value[1]][$value[0]->toRfc4122()];
                    } catch (ErrorException $e) {
                        $value = "{$value[1]}/{$value[0]->toRfc4122()}";
                    }
                }

                return [
                    Datum::class,
                    [
                        "field" => $field,
                        "formRow" => $field->getFormRow(),
                        "datum" => $value,
                    ],
                ];
            });
        };

        $conditionColumns = [];
        $dataSetColumns = [];
        $comparisonColumns = [];

        /** @var ExperimentalDesignField $field */
        foreach ($run->getDesign()->getFields() as $field) {
            if ($field->getRole() === ExperimentalFieldRole::Condition) {
                $conditionColumns[] = $getComponentColumn($field, $entities);
            } elseif ($field->getRole() === ExperimentalFieldRole::Datum) {
                $dataSetColumns[] = $getComponentColumn($field, $entities);
            } elseif ($field->getRole() === ExperimentalFieldRole::Comparison) {
                $comparisonColumns[] = $getComponentColumn($field, $entities);
            }
        }

        return $this->render("parts/experiments/experiment_view.html.twig", [
            "design" => $run->getDesign(),
            "run" => $run,
            "toolbox" => new Toolbox([
                new Tool(
                    $this->generateUrl("app_experiments_view", ["design" => $run->getDesign()->getId()]),
                    icon: "up",
                    buttonClass: "btn-secondary",
                    tooltip: "Return to run overview",
                ),
                new Tool(
                    $this->generateUrl("app_api_experiments_run_view_data", ["run" => $run->getId()]),
                    icon: "download",
                    buttonClass: "btn-secondary",
                    tooltip: "Download data as tsv",
                ),
                new EditTool(
                    path: $this->generateUrl("app_experiments_run_edit", ["run" => $run->getId()]),
                    tooltip: "Edit run",
                ),
                new EditTool(
                    path: $this->generateUrl("app_experiments_run_addData", ["run" => $run->getId()]),
                    icon: "data",
                    tooltip: "Edit run data",
                    iconStack: "edit",
                ),
            ]),
            "conditionTable" => new Table(
                data: $run->getConditions(),
                columns: [
                    new Column("Name", fn(ExperimentalRunCondition $condition) => $condition->getName()),
                    new ComponentColumn("Reference", function (ExperimentalRunCondition $condition) use ($conditionRepository) {
                        $referenceConditions = new ArrayCollection($conditionRepository->getReferenceConditions($condition));

                        return [
                            EntityReference::class, [
                                "entity" => $referenceConditions,
                                "displayMax" => 3,
                            ],
                        ];
                    }, widthRecommendation: 10),
                    new ToggleColumn("Control", fn(ExperimentalRunCondition $condition) => $condition->isControl()),
                    ... $conditionColumns,
                ],
            ),
            "datasetTable" => new Table(
                data: $run->getDataSets()->filter(fn(ExperimentalRunDataSet $dataSet) => $dataSet->getControlCondition() === null),
                columns: [
                    new Column("Condition", fn(ExperimentalRunDataSet $dataSet) => $dataSet->getCondition()->getName()),
                    ... $dataSetColumns,
                ],
            ),
            "comparisonTable" => new Table(
                data: $run->getDataSets()->filter(fn(ExperimentalRunDataSet $dataSet) => $dataSet->getControlCondition() !== null),
                columns: [
                    new Column("Condition", fn(ExperimentalRunDataSet $dataSet) => $dataSet->getCondition()->getName()),
                    new Column("Control", fn(ExperimentalRunDataSet $dataSet) => $dataSet->getControlCondition()?->getName()),
                    ... $comparisonColumns,
                ],
            ),
        ]);
    }
}
