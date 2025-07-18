<?php
declare(strict_types=1);

namespace App\Twig\Components\Live;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\Vendor;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToggleColumn;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\ClipwareTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Form\Search\AntibodySearchType;
use App\Form\Search\ChemicalSearchType;
use App\Form\Search\OligoSearchType;
use App\Form\Search\PlasmidSearchType;
use App\Form\Search\ProteinSearchType;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Service\Doctrine\Type\Ulid;
use App\Twig\Components\EntityReference;
use App\Twig\Components\ExternalUrl;
use App\Twig\Components\SmilesViewer;
use App\Twig\Components\Trait\PaginatedTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;
use ValueError;

/**
 * @template TSubstanceType of Substance
 */
#[AsLiveComponent]
final class SubstanceTable extends AbstractController
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;
    use PaginatedTrait;

    #[LiveProp]
    #[Assert\Choice(choices: ["antibody", "chemical", "oligo", "plasmid", "protein"])]
    public string $type;

    #[LiveProp]
    #[Assert\NotBlank]
    public string $liveSearchFormType;

    /** @var array<string, mixed> */
    #[LiveProp(url: true)]
    public array $search = [];

    /**
     * @var class-string<covariant TSubstanceType>
     */
    public string $entityType;

    /** @var EntityRepository<covariant TSubstanceType>  */
    public EntityRepository $entityRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * @param array<string, mixed> $props
     * @return array<string, mixed>
     */
    #[PreMount]
    public function preMount(array $props): array
    {
        [$props["entityType"], $props["liveSearchFormType"]] = match ($props["type"]) {
            "antibody" => [Antibody::class, AntibodySearchType::class],
            "chemical" => [Chemical::class, ChemicalSearchType::class],
            "oligo" => [Oligo::class, OligoSearchType::class],
            "plasmid" => [Plasmid::class, PlasmidSearchType::class],
            "protein" => [Protein::class, ProteinSearchType::class],
            default => throw new ValueError("Unsupported type for SubstanceTable component."),
        };

        $props["entityRepository"] = $this->entityManager->getRepository($props["entityType"]);


        return $props;
    }

    #[PostMount]
    public function postMount(): void
    {
        $this->validate(false);
    }

    public function getNumberOfRows(): int
    {
        if ($this->numberOfRows === null) {
            $repository = $this->getRepository();

            if ($repository instanceof PaginatedRepositoryInterface) {
                $maxResults = $repository->getPaginatedResultCount(
                    searchFields: $this->search,
                );
            } else {
                $data = $repository->findAll();
                $maxResults = count($data);
            }

            $this->setNumberOfRows($maxResults);
        }

        return $this->numberOfRows;
    }

    /**
     * @return Table<covariant TSubstanceType>|null
     */
    public function getTable(): ?Table
    {
        if (!$this->isValid()) {
            return null;
        }

        $table = match ($this->type) {
            "antibody" => $this->getAntibodyTable(),
            "chemical" => $this->getChemicalTable(),
            "oligo" => $this->getOligoTable(),
            "plasmid" => $this->getPlasmidTable(),
            "protein" => $this->getProteinTable(),
            default => null,
        };

        if ($table) {
            $this->addData($table);
        }

        return $table;
    }

    /**
     * @return EntityRepository<covariant TSubstanceType>
     */
    private function getRepository(): EntityRepository
    {
        if (!isset($this->entityRepository)) {
            $entityType = $this->getEntityClass();
            $this->entityRepository = $this->entityManager->getRepository($entityType);
        }

        return $this->entityRepository;
    }

    /**
     * @return class-string<TSubstanceType>
     */
    private function getEntityClass(): string
    {
        if (!isset($this->entityType)) {
            $this->entityType = match ($this->type) {
                "antibody" => Antibody::class,
                "chemical" => Chemical::class,
                "oligo" => Oligo::class,
                "plasmid" => Plasmid::class,
                "protein" => Protein::class,
                default => "",
            };
        }

        return $this->entityType;
    }

    /**
     * @param Table<covariant TSubstanceType> $table
     * @return void
     */
    private function addData(Table $table): void
    {
        $repository = $this->getRepository();

        if ($repository instanceof PaginatedRepositoryInterface) {
            $data = $repository->getPaginatedResults(
                searchFields: $this->search,
                page: $this->page,
                limit: $this->limit,
            );
        } else {

            $data = $repository->findAll();
        }

        $table->setData($data);  // @phpstan-ignore argument.type
        $table->setMaxRows($this->getNumberOfRows());
    }

    /**
     * @return Table<Antibody>
     */
    private function getAntibodyTable(): Table
    {
        $antibodyCitation = function (Antibody $antibody, ?Vendor $vendor, ?string $productNumber, ?string $rrid): string {
            $text = $antibody->getLongName();
            $moreInformation = [
                $vendor?->getName() ?? "??",
                $productNumber ?? "??",
            ];

            if ($rrid) {
                $moreInformation[] = "RRID:{$rrid}";
            }

            $moreInformation = implode(", ", $moreInformation);

            return "$text ($moreInformation)";
        };

        return new Table(
            data: [],
            columns: [
                new ToolboxColumn("", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => new Toolbox([
                    new ViewTool(
                        path: $this->generateUrl("app_antibody_view_number", ["antibodyNr" => $antibody->getNumber()]),
                        enabled: $this->isGranted("view", $antibody),
                        tooltip: "View Antibody",
                    ),
                    new ClipwareTool(
                        clipboardText: $antibodyCitation($antibody, $antibody->getVendor(), $antibody->getVendorPn(), $antibody->getRrid()),
                        tooltip: "Copy antibody citation",
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_substance_edit", ["substance" => $antibody->getUlid()]),
                        enabled: $this->isGranted("edit", $antibody),
                        tooltip: "Edit antibody",
                    ),
                    new AddTool(
                        path: $this->generateUrl("app_substance_add_lot", ["substance" => $antibody->getUlid()]),
                        enabled: $this->isGranted("add_lot", $antibody),
                        tooltip: "Add lot",
                    ),
                ])),
                new Column("Nr", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getNumber(), bold: true),
                new ComponentColumn("", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => [
                    "Icon",
                    [
                        "icon" => "antibody.{$antibody->getType()->value}",
                    ],
                ]),
                new Column("Type", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getType()->value),
                new Column("Available Lots (total)", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => "{$hasAvailableLot} ($lotCount)"),
                new Column(
                    "Name",
                    fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getShortName(),
                    tooltip: fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getLongName(),
                ),
                new ComponentColumn("Target Epitope", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => [
                    "EntityReference",
                    [
                        "entity" => $antibody->getEpitopeTargets(),
                    ],
                ]),

                new ComponentColumn("AB Epitope", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => [
                    "EntityReference",
                    [
                        "entity" => $antibody->getEpitopes(),
                    ],
                ]),
                new ToggleColumn("Validated internally", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getValidatedInternally()),
                new ToggleColumn("Validated externally", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getValidatedExternally()),
                new ComponentColumn("RRID", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => [
                    "ExternalUrl",
                    [
                        "title" => $antibody->getRrid(),
                        "href" => "https://scicrunch.org/resolver/{$antibody->getRrid()}",
                    ],
                ]),
            ],
            spreadDatum: true,
            isDisabled: fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $hasAvailableLot === 0,
        );
    }

    #[LiveListener("search.antibody")]
    public function onAntibodySearch(
        #[LiveArg]
        ?string $antibodyNumber = null,
        #[LiveArg]
        ?string $antibodyType = null,
        #[LiveArg]
        ?string $antibodyName = null,
        #[LiveArg]
        ?string $hasAvailableLots = null,
        #[LiveArg]
        ?string $internallyValidated = null,
        #[LiveArg]
        ?string $externallyValidated = null,
        #[LiveArg]
        ?string $rrid = null,
        #[LiveArg]
        ?string $hasEpitope = null,
        #[LiveArg]
        ?string $targetsEpitope = null,
        #[LiveArg]
        ?string $productNumber = null,
    ): void {
        $this->search = [
            "antibodyNumber" => $antibodyNumber,
            "antibodyType" => $antibodyType,
            "antibodyName" => $antibodyName,
            "hasAvailableLot" => match ($hasAvailableLots) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "internallyValidated" => match ($internallyValidated) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "externallyValidated" => match ($externallyValidated) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "rrid" => $rrid,
            "hasEpitope" => $hasEpitope === null ? null : Ulid::fromString($hasEpitope)->toRfc4122(),
            "targetsEpitope" => $targetsEpitope === null ? null : Ulid::fromString($targetsEpitope)->toRfc4122(),
            "productNumber" => $productNumber,
        ];

        $this->page = 0;
    }

    /**
     * @return Table<Chemical>
     */
    private function getChemicalTable(): Table
    {
        return new Table(
            data: [],
            columns: [
                new ToolboxColumn("", fn(Chemical $chemical, int $lotCount, int $hasAvailableLot) => new Toolbox([
                    new ViewTool(
                        path: $this->generateUrl("app_substance_view", ["substance" => $chemical->getUlid()]),
                        enabled: $this->isGranted("view", $chemical),
                        tooltip: "View Chemical",
                    ),
                    new ClipwareTool(
                        clipboardText: $chemical->getCitation(),
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_substance_edit", ["substance" => $chemical->getUlid()]),
                        enabled: $this->isGranted("edit", $chemical),
                        tooltip: "Edit chemical",
                    ),
                    new AddTool(
                        path: $this->generateUrl("app_substance_add_lot", ["substance" => $chemical->getUlid()]),
                        enabled: $this->isGranted("add_lot", $chemical),
                        tooltip: "Add lot",
                    ),
                ])),
                new ComponentColumn("Structure", fn(Chemical $chemical, int $lotCount, int $hasAvailableLot) => [
                    SmilesViewer::class, [
                        "key" => $chemical->getUlid()->toRfc4122(),
                        "smiles" => $chemical->getSmiles(),
                        "padding" => 2,
                    ],
                ], widthRecommendation: 10),
                new Column("Name", fn(Chemical $chemical, int $lotCount, int $hasAvailableLot) => $chemical->getShortName(), bold: true),
                new Column("CAS", fn(Chemical $chemical, int $lotCount, int $hasAvailableLot) => $chemical->getCasNumber()),
                new Column("Available Lots (total)", fn(Chemical $chemical, int $lotCount, int $hasAvailableLot) => "{$hasAvailableLot} ($lotCount)"),
            ],
            spreadDatum: true,
        );
    }

    #[LiveListener("search.chemical")]
    public function onChemicalSearch(
        #[LiveArg]
        ?string $shortName = null,
        #[LiveArg]
        ?string $anyName = null,
        #[LiveArg]
        ?string $casNumber = null,
        #[LiveArg]
        ?string $hasAvailableLots = null,
    ): void {
        $this->search = [
            "shortName" => $shortName,
            "anyName" => $anyName,
            "casNumber" => $casNumber,
            "hasAvailableLot" => match ($hasAvailableLots) {
                "true" => true,
                "false" => false,
                default => null,
            },
        ];

        $this->page = 0;
    }

    /**
     * @return Table<Oligo>
     */
    private function getOligoTable(): Table
    {
        return new Table(
            data: [],
            columns: [
                new ToolboxColumn("", fn(Oligo $oligo, int $lotCount, int $hasAvailableLot) => new Toolbox([
                    new ViewTool(
                        path: $this->generateUrl("app_substance_view", ["substance" => $oligo->getUlid()]),
                        enabled: $this->isGranted("view", $oligo),
                        tooltip: "View Chemical",
                    ),
                    new ClipwareTool(
                        clipboardText: $oligo->getCitation(),
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_substance_edit", ["substance" => $oligo->getUlid()]),
                        enabled: $this->isGranted("edit", $oligo),
                        tooltip: "Edit chemical",
                    ),
                    new AddTool(
                        path: $this->generateUrl("app_substance_add_lot", ["substance" => $oligo->getUlid()]),
                        enabled: $this->isGranted("add_lot", $oligo),
                        tooltip: "Add lot",
                    ),
                ])),
                new Column("Name", fn(Oligo $oligo, int $lotCount, int $hasAvailableLot) => $oligo->getShortName(), bold: true),
                new Column("Type", fn(Oligo $oligo, int $lotCount, int $hasAvailableLot) => $oligo->getOligoTypeEnum()?->value),
                new Column("Length", fn(Oligo $oligo, int $lotCount, int $hasAvailableLot) => $oligo->getSequenceLength()),
                new Column("Available Lots (total)", fn(Oligo $oligo, int $lotCount, int $hasAvailableLot) => "{$hasAvailableLot} ($lotCount)"),
                new ComponentColumn("Start conjugate", fn(Oligo $oligo, int $lotCount, int $hasAvailableLot) => [
                    EntityReference::class,
                    ["entity" => $oligo->getStartConjugate()],
                ]),
                new ComponentColumn("End conjugate", fn(Oligo $oligo, int $lotCount, int $hasAvailableLot) => [
                    EntityReference::class,
                    ["entity" => $oligo->getEndConjugate()],
                ]),
                new Column("Sequence", fn(Oligo $oligo, int $lotCount, int $hasAvailableLot) => $oligo->getSequence()),
            ],
            spreadDatum: true,
        );
    }

    #[LiveListener("search.oligo")]
    public function onOligoSearch(
        #[LiveArg]
        ?string $shortName = null,
        #[LiveArg]
        ?string $anyName = null,
        #[LiveArg]
        ?string $sequence = null,
        #[LiveArg]
        ?string $hasAvailableLots = null,
        #[LiveArg]
        ?string $oligoType = null,
        #[LiveArg]
        ?string $startConjugate = null,
        #[LiveArg]
        ?string $endConjugate = null,
    ): void {
        $this->search = [
            "shortName" => $shortName,
            "anyName" => $anyName,
            "oligoType" => $oligoType,
            "sequence" => $sequence,
            "hasAvailableLot" => match ($hasAvailableLots) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "startConjugate" => $startConjugate === null ? null : Ulid::fromString($startConjugate)->toRfc4122(),
            "endConjugate" => $endConjugate === null ? null : Ulid::fromString($endConjugate)->toRfc4122(),
        ];

        $this->page = 0;
    }

    /**
     * @return Table<Plasmid>
     */
    private function getPlasmidTable(): Table
    {
        return new Table(
            data: [],
            columns: [
                new ToolboxColumn("", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => new Toolbox([
                    new ViewTool(
                        path: $this->generateUrl("app_substance_view", ["substance" => $plasmid->getUlid()]),
                        enabled: $this->isGranted("view", $plasmid),
                        tooltip: "View Plasmid",
                    ),
                    new ClipwareTool(
                        clipboardText: $plasmid->getCitation(),
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_substance_edit", ["substance" => $plasmid->getUlid()]),
                        enabled: $this->isGranted("edit", $plasmid),
                        tooltip: "Edit plasmid",
                    ),
                    new AddTool(
                        path: $this->generateUrl("app_substance_add_lot", ["substance" => $plasmid->getUlid()]),
                        enabled: $this->isGranted("add_lot", $plasmid),
                        tooltip: "Add lot",
                    ),
                ])),
                new Column("Number", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => $plasmid->getNumber(), bold: true),
                new Column("Name", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => $plasmid->getShortName()),
                new Column("Length (kbp)", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => $plasmid->getSequenceLength() / 1000),
                new Column("Available Lots (total)", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => "{$hasAvailableLot} ($lotCount)"),
                new Column("Plasmid growth resistance", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => implode(", ", $plasmid->getGrowthResistance())),
                new ComponentColumn("Expressed protein", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => [
                    EntityReference::class,
                    [
                        "entity" => $plasmid->getExpressedProteins(),
                    ],
                ]),
                new Column("Expression host", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => $plasmid->getExpressionIn()),
                new Column("Expression resistance", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => implode(", ", $plasmid->getExpressionResistance())),
                new ToggleColumn("For production", fn(Plasmid $plasmid, int $lotCount, int $hasAvailableLot) => $plasmid->isForProduction()),
            ],
            spreadDatum: true,
        );
    }

    #[LiveListener("search.plasmid")]
    public function onPlasmidSearch(
        #[LiveArg]
        ?string $number = null,
        #[LiveArg]
        ?string $shortName = null,
        #[LiveArg]
        ?string $anyName = null,
        #[LiveArg]
        ?string $sequence = null,
        #[LiveArg]
        ?string $hasAvailableLots = null,
        #[LiveArg]
        ?string $growthResistance = null,
        #[LiveArg]
        ?string $expressionResistance = null,
        #[LiveArg]
        ?string $expressionOrganism = null,
        #[LiveArg]
        ?string $expressedProtein = null,
        #[LiveArg]
        ?string $expressesProtein = null,
    ): void {
        $this->search = [
            "number" => $number,
            "shortName" => $shortName,
            "anyName" => $anyName,
            "sequence" => $sequence,
            "hasAvailableLot" => match ($hasAvailableLots) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "expressesProtein" => match ($expressesProtein) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "growthResistance" => $growthResistance,
            "expressionResistance" => $expressionResistance,
            "expressionOrganism" => $expressionOrganism !== null ? intval($expressionOrganism) : null,
            "expressedProtein" => $expressedProtein === null ? null : Ulid::fromString($expressedProtein)->toRfc4122(),
        ];

        $this->page = 0;
    }

    /**
     * @return Table<Protein>
     */
    private function getProteinTable(): Table
    {
        $getLastElementOfArray = function (array $array) {
            $last_key = array_key_last($array);
            return $array[$last_key];
        };

        return new Table(
            data: [],
            columns: [
                new ToolboxColumn("", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => new Toolbox([
                    new ViewTool(
                        path: $this->generateUrl("app_substance_view", ["substance" => $protein->getUlid()]),
                        enabled: $this->isGranted("view", $protein),
                        tooltip: "View Protein",
                    ),
                    new ClipwareTool(
                        clipboardText: $protein->getCitation(),
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_substance_edit", ["substance" => $protein->getUlid()]),
                        enabled: $this->isGranted("edit", $protein),
                        tooltip: "Edit plasmid",
                    ),
                    new AddTool(
                        path: $this->generateUrl("app_substance_add_lot", ["substance" => $protein->getUlid()]),
                        enabled: $this->isGranted("add_lot", $protein),
                        tooltip: "Add lot",
                    ),
                ])),
                new Column("Name", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => $protein->getShortName()),
                new ComponentColumn("Protein Atlas", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => [
                    ExternalUrl::class, [
                        "title" => $protein->getProteinAtlasUri() === null ? "" : $getLastElementOfArray(explode("/", $protein->getProteinAtlasUri())),
                        "href" => $protein->getProteinAtlasUri(),
                    ],
                ]),
                new Column("Origin organism", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => $protein->getOrganism()),
                new Column("Length (aa)", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => $protein->getFastaSequence() !== null ? strlen($protein->getFastaSequence()) : 0),
                new Column("Available Lots (total)", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => "{$hasAvailableLot} ($lotCount)"),
                /*new ComponentColumn("Parent proteins", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => [
                    EntityReference::class,
                    [
                        "entity" => $protein->getParents(),
                    ]
                ]),
                new ComponentColumn("Derived proteins", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => [
                    EntityReference::class,
                    [
                        "entity" => $protein->getChildren(),
                    ]
                ]),*/
                new ComponentColumn("Epitopes", fn(Protein $protein, int $lotCount, int $hasAvailableLot) => [
                    EntityReference::class,
                    [
                        "entity" => $protein->getEpitopes(),
                    ],
                ]),
            ],
            spreadDatum: true,
        );
    }

    #[LiveListener("search.protein")]
    public function onProteinSearch(
        #[LiveArg]
        ?string $shortName = null,
        #[LiveArg]
        ?string $anyName = null,
        #[LiveArg]
        ?string $sequence = null,
        #[LiveArg]
        ?string $hasAvailableLots = null,
        #[LiveArg]
        ?string $hasAntibodies = null,
        #[LiveArg]
        ?string $originOrganism = null,
    ): void {
        $this->search = [
            "shortName" => $shortName,
            "anyName" => $anyName,
            "sequence" => $sequence,
            "hasAvailableLot" => match ($hasAvailableLots) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "hasAntibodies" => match ($hasAntibodies) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "originOrganism" => $originOrganism !== null ? intval($originOrganism) : null,
        ];

        $this->page = 0;
    }
}
