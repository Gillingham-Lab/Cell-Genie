<?php
declare(strict_types=1);

namespace App\Twig\Components\Live;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Vendor;
use App\Entity\Epitope;
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
use App\Genie\Enums\AntibodyType;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Service\Doctrine\Type\Ulid;
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

#[AsLiveComponent]
final class SubstanceTable extends AbstractController
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;
    use PaginatedTrait;

    #[LiveProp]
    #[Assert\Choice(choices: ["antibody"])]
    public string $type;

    #[LiveProp]
    #[Assert\NotBlank]
    public string $liveSearchFormType;

    #[LiveProp(url: true)]
    public array $search = [];

    public string $entityType;

    public EntityRepository $entityRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[PreMount]
    public function preMount($props)
    {
        $props["entityType"] = match ($props["type"]) {
            "antibody" => Antibody::class,
            default => null,
        };

        $props["entityRepository"] = $props["entityType"] == null ? null : $this->entityManager->getRepository($props["entityType"]);

        $props["liveSearchFormType"] = match($props["type"]) {
            "antibody" => AntibodySearchType::class
        };

        return $props;
    }

    #[PostMount]
    public function postMount()
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

    public function getTable(): ?Table
    {
        if (!$this->isValid()) {
            return null;
        }

        $table = match($this->type) {
            "antibody" => $this->getAntibodyTable(),
        };

        $this->addData($table);

        return $table;
    }

    private function getRepository(): EntityRepository
    {
        if (!isset($this->entityRepository)) {
            $entityType = $this->getEntityClass();
            $this->entityRepository = $this->entityManager->getRepository($entityType);
        }

        return $this->entityRepository;
    }

    private function getEntityClass(): string
    {
        if (!isset($this->entityType)) {
            $this->entityType = match ($this->type) {
                "antibody" => Antibody::class,
                default => null,
            };
        }

        return $this->entityType;
    }

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

        $table->setData($data);
        $table->setMaxRows($this->getNumberOfRows());
    }

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
                    )
                ])),
                new Column("Nr", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getNumber(), bold: true),
                new ComponentColumn("", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => [
                    "Icon",
                    [
                        "icon" => "antibody.{$antibody->getType()->value}"
                    ]
                ]),
                new Column("Type", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getType()->value),
                new Column("Available Lots (total)", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => "{$hasAvailableLot} ($lotCount)"),
                new Column("Name", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getShortName()),
                new ComponentColumn("Target Epitope", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => [
                    "EntityReference",
                    [
                        "entity" => $antibody->getEpitopeTargets(),
                    ]
                ]),

                new ComponentColumn("AB Epitope", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => [
                    "EntityReference",
                    [
                        "entity" => $antibody->getEpitopes(),
                    ]
                ]),
                new ToggleColumn("Validated internally", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getValidatedInternally()),
                new ToggleColumn("Validated externally", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $antibody->getValidatedExternally()),
                new ComponentColumn("RRID", fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => [
                    "ExternalUrl",
                    [
                        "title" => $antibody->getRrid(),
                        "href" => "https://scicrunch.org/resolver/{$antibody->getRrid()}",
                    ]
                ]),
            ],
            spreadDatum: true,
            isDisabled: fn(Antibody $antibody, int $lotCount, int $hasAvailableLot) => $hasAvailableLot === 0,
        );
    }

    #[LiveListener("search.antibody")]
    public function onAntibodySearch(
        #[LiveArg] ?string $antibodyNumber = null,
        #[LiveArg] ?string $antibodyType = null,
        #[LiveArg] ?string $antibodyName = null,
        #[LiveArg] ?string $hasAvailableLots = null,
        #[LiveArg] ?string $internallyValidated = null,
        #[LiveArg] ?string $externallyValidated = null,
        #[LiveArg] ?string $rrid = null,
        #[LiveArg] ?string $hasEpitope = null,
        #[LiveArg] ?string $targetsEpitope = null,
    ): void {
        $this->search = [
            "antibodyNumber" => $antibodyNumber,
            "antibodyType" => $antibodyType,
            "antibodyName" => $antibodyName,
            "hasAvailableLot" => match($hasAvailableLots) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "internallyValidated" => match($internallyValidated) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "externallyValidated" => match($externallyValidated) {
                "true" => true,
                "false" => false,
                default => null,
            },
            "rrid" => $rrid,
            "hasEpitope" => $hasEpitope === null ? null : Ulid::fromString($hasEpitope)->toRfc4122(),
            "targetsEpitope" => $targetsEpitope === null ? null : Ulid::fromString($targetsEpitope)->toRfc4122(),
        ];

        $this->page = 0;
    }
}