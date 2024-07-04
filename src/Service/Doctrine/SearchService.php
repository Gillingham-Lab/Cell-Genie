<?php
declare(strict_types=1);

namespace App\Service\Doctrine;

use App\Service\Doctrine\Type\Ulid;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class SearchService
{
    private $counter = 0;

    public function parse(string $searchValue): string
    {
        if (str_starts_with($searchValue, "^")) {
            $searchValue = substr($searchValue, 1);
        } elseif (!str_starts_with($searchValue, "%")) {
            $searchValue = "%" . $searchValue;
        }

        if (str_ends_with($searchValue, "$")) {
            $searchValue = substr($searchValue, 0, strlen($searchValue)-1);
        } elseif (!str_ends_with($searchValue, "%")) {
            $searchValue = $searchValue . "%";
        }

        return mb_strtolower($searchValue);
    }

    public function searchWith(QueryBuilder $qb, string|array $field, string $type, mixed $value)
    {
        if (is_array($field)) {
            $expressions = [];
            foreach ($field as $f) {
                $expressions[] = $this->searchWith($qb, $f, $type, $value);
            }
            return $qb->expr()->orX(...$expressions);
        } else {
            return match($type) {
                "string" => $this->searchWithStringLike($qb, $field, $value),
                "int" => $this->searchWithInteger($qb, $field, $value),
                "ulid" => $this->searchWithUlid($qb, $field, $value),
                "bool" => $this->searchWithBool($qb, $field, $value),
                default => $this->searchWithString($qb, $field, $value),
            };
        }
    }

    private function getFieldName()
    {
        $fieldName = "searchService{$this->counter}";
        $this->counter++;
        return $fieldName;
    }

    public function searchWithString(QueryBuilder $qb, string $field, mixed $value): Query\Expr\Comparison
    {
        $fieldName = $this->getFieldName();
        $qb->setParameter($fieldName, $value);
        return $qb->expr()->eq($field, ":$fieldName");
    }

    public function searchWithStringLike(QueryBuilder $qb, string $field, string $value): Query\Expr\Comparison
    {
        $fieldName = $this->getFieldName();
        $value = $this->parse($value);

        $qb->setParameter($fieldName, $value);
        return $qb->expr()->like($qb->expr()->lower($field), ":$fieldName");
    }

    public function searchWithInteger(QueryBuilder $qb, string $field, int|string $value): Query\Expr\Comparison
    {
        if (is_string($value)) {
            $value = intval($value);
        }

        return $this->searchWithString($qb, $field, $value);
    }

    public function searchWithUlid(QueryBuilder $qb, string $field, string $value): Query\Expr\Comparison
    {
        $value = Ulid::fromString($value)->toRfc4122();
        return $this->searchWithString($qb, $field, $value);
    }

    public function searchWithBool(QueryBuilder $qb, string $field, mixed $value): Query\Expr\Comparison
    {
        if (is_bool($value)) {
            return $qb->expr()->eq($field, $value ? "true" : "false");
        } else {
            return $qb->expr()->eq($field, $value === "true" ? "true" : "false");
        }
    }
}