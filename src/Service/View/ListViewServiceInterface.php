<?php
declare(strict_types=1);

namespace App\Service\View;

use App\Entity\Toolbox\Toolbox;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @template T of object
 * @template-covariant T
 */
interface ListViewServiceInterface
{
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Security $security,
    );

    /**
     * @param T[] $items
     * @return T[]
     */
    public function sortItems(array $items): array;

    public function getItemIcon(): ?string;

    /**
     * @param T $item
     * @return string
     */
    public function getItemLabel(object $item): string;

    /**
     * @param T $item
     * @return string
     */
    public function getItemUrl(object $item): string;

    /**
     * @param T $item
     * @return Toolbox
     */
    public function getItemTools(object $item): ?Toolbox;

    /**
     * @param T $item
     * @return null|array{class-string, array<string, mixed>}|array{null, string}
     */
    public function getPostItemComponent(object $item): ?array;

    /**
     * @param T $item
     * @return bool
     */
    public function isEmpty(object $item): bool;
}