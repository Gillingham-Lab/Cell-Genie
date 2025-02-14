<?php
declare(strict_types=1);

namespace App\Service;

class CacheKeyService
{
    /**
     * @param list<mixed> $collection
     */
    public function getCacheKeyFromCollection(array $collection, string $prefix = ""): string
    {
        $key = "";
        foreach ($collection as $item) {
            if (is_object($item)) {
                if (method_exists($item, "getId")) {
                    $key .= $item->getId();
                } elseif (method_exists($item, "getUlid")) {
                    $key .= $item->getUlid();
                } else {
                    $key .= (string)$item;
                }
            } else {
                $key .= (string)$item;
            }
        }

        return $this->getCacheKeyFromString($key, $prefix);
    }

    public function getCacheKeyFromString(string $string, string $prefix = ""): string
    {
        return $prefix . hash("sha256", $string);
    }
}