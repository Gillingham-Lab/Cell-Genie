<?php
declare(strict_types=1);

namespace App\Twig\Components\Cell;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Repository\Cell\CellCultureRepository;
use DateTimeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class CellCultureDiagram
{
    public DateTimeInterface $startDate;
    public DateTimeInterface $endDate;

    public ?string $incubatorFilter;
    public ?string $scientistFilter;
    /** @var array<string, CellCulture> */
    public array $cultures = [];
    public ?CellCulture $culture;

    public int $leftMargin = 210;

    public function __construct(
        private readonly CellCultureRepository $cultureRepository
    ) {

    }

    /**
     * @param array<string, mixed> $props
     * @return array{startDate: DateTimeInterface, endDate: DateTimeInterface, incubatorFilter?: ?string, scientistFilter?: ?string, leftMargin: int}
     */
    #[PreMount]
    public function preMount(array $props): array
    {
        // Validate
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            "incubatorFilter" => null,
            "scientistFilter" => null,
            "leftMargin" => 210,
            "culture" => null,
        ]);

        $resolver->setAllowedTypes("incubatorFilter", ["null", "string"]);
        $resolver->setAllowedTypes("scientistFilter", ["null", "string"]);
        $resolver->setAllowedTypes("leftMargin", "int");
        $resolver->setAllowedTypes("culture", ["null", CellCulture::class]);

        $resolver->setRequired([
            "startDate",
            "endDate",
        ]);

        $resolver->setAllowedTypes("startDate", "DateTimeInterface");
        $resolver->setAllowedTypes("endDate", "DateTimeInterface");

        return $resolver->resolve($props) + $props;
    }

    #[PostMount]
    public function postMount(): void
    {
        if ($this->culture !== null) {
            $this->cultures = [$this->culture->getId()->toBase58() => $this->culture];
            return;
        }

        $currentCultures = $this->cultureRepository->findAllBetween(
            $this->startDate,
            $this->endDate,
            $this->incubatorFilter,
            $this->scientistFilter,
        );

        $cultures = [];
        foreach ($currentCultures as $culture) {
            // Skip if already set
            if (isset($cultures[$culture->getId()->toBase58()])) {
                continue;
            }

            // Skip if it has a parent culture registered (for group reasons).
            if ($culture->getParentCellCulture() !== null) {
                continue;
            }

            // Add
            $cultures[$culture->getId()->toBase58()] = $culture;

            // Now we add all child cultures of the current culture.
            foreach ($culture->getSubCellCultures() as $subCulture) {
                $cultures[$subCulture->getId()->toBase58()] = $subCulture;
            }
        }

        $this->cultures = $cultures;
    }
}