<?php
declare(strict_types=1);

namespace App\Service\View;

use Doctrine\Common\Collections\Collection;

trait DefaultTreeViewTrait
{
    public function isIconStacked(?object $node = null): bool
    {
        return true;
    }

    public function isIterable(?object $node = null): bool
    {
        return $node->getChildren()->count() > 0;
    }

    public function getTree(object $node): array
    {
        return $node->getChildren()->toArray();
    }

    public function isCurrentPath(object $node, object $object = null): bool
    {
        if ($node === $object) {
            return true;
        } elseif ($object !== null and $object->getParent()) {
            return $this->isCurrentPath($node, $object->getParent());
        } else {
            return false;
        }
    }
}