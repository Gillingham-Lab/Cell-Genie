<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Toolbox\Toolbox as ToolboxEntity;
use App\Service\View\TreeViewServiceInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @template T of object
 * @template-covariant T
 * @implements TreeViewServiceInterface<T>
 * @phpstan-type TreeViewMountInputData array{
 *      tree: iterable<T>,
 *      currentNode: null|T,
 *      service: class-string<TreeViewServiceInterface<T>>|TreeViewServiceInterface<T>,
 *      treeLevel: int,
 *      childComponentParams: array<string, mixed>,
 * }
 * @phpstan-type TreeViewMountReturnData array{
 *      tree: list<T>,
 *      currentNode: null|T,
 *      service: TreeViewServiceInterface<T>,
 *      treeLevel: int,
 *      childComponentParams: array<string, mixed>,
 * }
 */
#[AsTwigComponent]
class TreeView implements TreeViewServiceInterface
{
    /**  @var list<T> */
    public array $tree = [];

    /** @var null|T */
    public ?object $currentNode = null;

    public int $treeLevel = 0;

    /** @var TreeViewServiceInterface<T>  */
    public ?TreeViewServiceInterface $service = null;

    /** @var array<string, mixed> */
    public array $childComponentParams = [];

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {}

    /**
     * @param TreeViewMountInputData $data
     * @return TreeViewMountReturnData
     */
    #[PreMount]
    public function preMount(
        array $data,
    ): array {
        if ($data["tree"] instanceof Collection) {
            $data["tree"] = $data["tree"]->toArray();
        }

        if (isset($data["service"])) {
            // Create a new service class - if it is not already one anyway
            if (!is_object($data["service"])) {
                $data["service"] = new $data["service"](
                    $this->urlGenerator,
                    $this->security,
                );
            }
        } else {
            $data["service"] = null;
        }

        return $data;
    }

    /**
     * @param T $node
     * @return bool
     */
    public function isActive(object $node): bool
    {
        return $this->currentNode === $node;
    }

    public function getNodeIcon(?object $node = null): ?string
    {
        return $this->service?->getNodeIcon($node);
    }

    public function isIconStacked(?object $node = null): bool
    {
        return $this->service?->isIconStacked($node);
    }

    public function getNodeLabel(object $node): string
    {
        return $this->service?->getNodeLabel($node) ?? (string) $node;
    }

    public function getNodeUrl(object $node): string
    {
        return $this->service?->getNodeUrl($node) ?? "";
    }

    public function getNodeTools(object $node): ?ToolboxEntity
    {
        return $this->service?->getNodeTools($node);
    }

    public function getPostNodeComponent(object $node): ?array
    {
        return $this->service?->getPostNodeComponent($node);
    }

    public function getPreChildComponent(object $node): ?array
    {
        return $this->service?->getPreChildComponent($node);
    }

    public function getPostChildComponent(object $node): ?array
    {
        return $this->service?->getPostChildComponent($node);
    }

    public function isIterable(object $node): bool
    {
        return $this->service?->isIterable($node) ?? false;
    }

    public function getTree(object $node): array
    {
        return $this->service?->getTree($node);
    }

    public function isCurrentPath(object $node, ?object $object = null): bool
    {
        return $this->service?->isCurrentPath($node, $this->currentNode);
    }
}
