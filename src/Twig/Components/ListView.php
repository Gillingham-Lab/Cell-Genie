<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Toolbox\Toolbox;
use App\Service\View\ListViewServiceInterface;
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
 */
#[AsTwigComponent]
class ListView implements ListViewServiceInterface
{
    /** @var list<T> */
    public iterable $items;

    /** @var TreeViewServiceInterface<T>  */
    public ListViewServiceInterface $service;

    /** @var T|null */
    public ?object $currentItem = null;
    public bool $sort = true;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    #[PreMount]
    public function preMount(array $data): array
    {
        if ($data["items"] instanceof Collection) {
            $data["items"] = $data["items"]->toArray();
        }

        if (isset($data["service"])) {
            if (!is_object($data["service"])) {
                $data["service"] = new $data["service"](
                    $this->urlGenerator,
                    $this->security,
                );
            }

            $data["items"] = $data["service"]->sortItems($data["items"]);
        } else {
            $data["service"] = null;
        }

        return $data;
    }

    public function sortItems(array $items): array
    {
        return $this->service->sortItems($items);
    }

    /**
     * @param T $node
     * @return bool
     */
    public function isActive(object $node): bool
    {
        return $this->currentItem === $node;
    }

    public function getItemIcon(): ?string
    {
        return $this->service->getItemIcon();
    }

    public function getItemLabel(object $item): string
    {
        return $this->service->getItemLabel($item);
    }

    public function getItemUrl(object $item): string
    {
        return $this->service->getItemUrl($item);
    }

    public function getItemTools(object $item): ?Toolbox
    {
       return $this->service->getItemTools($item);
    }

    public function getPostItemComponent(object $item): ?array
    {
        return $this->service->getPostItemComponent($item);
    }

    public function isEmpty(object $item): bool
    {
        return $this->service->isEmpty($item);
    }
}