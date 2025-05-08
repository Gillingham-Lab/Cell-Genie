<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Toolbox\Toolbox as ToolboxEntity;
use App\Service\TreeView\TreeViewServiceInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @template T of object
 * @template-covariant T
 * @implements TreeViewServiceInterface<T>
 */
#[AsTwigComponent]
class TreeView implements TreeViewServiceInterface
{
    /**
     * @var list<T>
     */

    public array $tree = [];

    /**
     * @var null|T
     */
    public ?object $currentNode = null;

    public int $treeLevel = 0;

    /** @var TreeViewServiceInterface<T>  */
    public ?TreeViewServiceInterface $service = null;

    /**
     * @var array<string, mixed>
     */
    public array $childComponentParams = [];

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
    ) {
    }

    /**
     * @param array{
     *     tree: iterable<T>,
     *     currentNode: null|T,
     *     service: class-string<T>,
     *     treeLevel: int,
     * } $data
     * @return array{
     *     tree: list<T>,
     *     currentNode: null|T,
     *     service: TreeViewServiceInterface<T>,
     *     treeLevel: int,
     * }
     */
    #[PreMount]
    public function preMount(
        array $data,
    ): array {
        if ($data["tree"] instanceof Collection) {
            $data["tree"] = $data["tree"]->toArray();
        }

        if (!empty($data["service"])) {
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

    public function getNodeIcon(): ?string
    {
        return $this->service?->getNodeIcon();
    }

    public function getNodeLabel(object $node): string
    {
        return $this->service?->getNodeLabel($node) ?? (string)$node;
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
}