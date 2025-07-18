<?php
declare(strict_types=1);

namespace App\Service\Doctrine;

use App\Service\Doctrine\Type\Ulid;
use Closure;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class SearchService
{
    private int $counter = 0;

    public function parse(string $searchValue): string
    {
        if (str_starts_with($searchValue, "^")) {
            $searchValue = substr($searchValue, 1);
        } elseif (!str_starts_with($searchValue, "%")) {
            $searchValue = "%" . $searchValue;
        }

        if (str_ends_with($searchValue, "$")) {
            $searchValue = substr($searchValue, 0, strlen($searchValue) - 1);
        } elseif (!str_ends_with($searchValue, "%")) {
            $searchValue = $searchValue . "%";
        }

        return mb_strtolower($searchValue);
    }

    /**
     * @param string|string[] $field
     */
    public function searchWith(QueryBuilder $qb, string|array $field, string $type, mixed $value): Orx|Comparison
    {
        if (is_array($field)) {
            $expressions = [];
            foreach ($field as $f) {
                $expressions[] = $this->searchWith($qb, $f, $type, $value);
            }
            return $qb->expr()->orX(...$expressions);
        } else {
            return match ($type) {
                "string" => $this->searchWithStringLike($qb, $field, $value),
                "int" => $this->searchWithInteger($qb, $field, $value),
                "ulid" => $this->searchWithUlid($qb, $field, $value),
                "bool" => $this->searchWithBool($qb, $field, $value),
                default => $this->searchWithString($qb, $field, $value),
            };
        }
    }

    private function getFieldName(): string
    {
        $fieldName = "searchService{$this->counter}";
        $this->counter++;
        return $fieldName;
    }

    public function searchWithString(QueryBuilder $qb, string $field, mixed $value): Comparison
    {
        $fieldName = $this->getFieldName();
        $qb->setParameter($fieldName, $value);
        return $qb->expr()->eq($field, ":$fieldName");
    }

    public function searchWithStringLike(QueryBuilder $qb, string $field, string $value): Comparison
    {
        $fieldName = $this->getFieldName();
        $value = $this->parse($value);

        $qb->setParameter($fieldName, $value);
        return $qb->expr()->like((string) $qb->expr()->lower($field), ":$fieldName");
    }

    public function searchWithInteger(QueryBuilder $qb, string $field, int|string $value): Comparison
    {
        if (is_string($value)) {
            $value = intval($value);
        }

        return $this->searchWithString($qb, $field, $value);
    }

    public function searchWithUlid(QueryBuilder $qb, string $field, string $value): Comparison
    {
        $value = Ulid::fromString($value)->toRfc4122();
        return $this->searchWithString($qb, $field, $value);
    }

    public function searchWithBool(QueryBuilder $qb, string $field, mixed $value): Comparison
    {
        if (is_bool($value)) {
            return $qb->expr()->eq($field, $value ? "true" : "false");
        } else {
            return $qb->expr()->eq($field, $value === "true" ? "true" : "false");
        }
    }

    /**
     * @param array<int, Orx|Andx|Comparison|Func|string> $expressions
     */
    public function addExpressionsToSearchQuery(QueryBuilder $queryBuilder, array $expressions): QueryBuilder
    {
        return match (count($expressions)) {
            0 => $queryBuilder,
            1 => $queryBuilder->andWhere($expressions[0]),
            default => $queryBuilder->andWhere($queryBuilder->expr()->andX(...$expressions)),
        };
    }

    /**
     * @param array<int, Orx|Andx|Comparison|Func|string> $expressions
     */
    public function addExpressionsToHavingQuery(QueryBuilder $queryBuilder, array $expressions): QueryBuilder
    {
        return match (count($expressions)) {
            0 => $queryBuilder,
            1 => $queryBuilder->andHaving($expressions[0]),
            default => $queryBuilder->andHaving($queryBuilder->expr()->andX(...$expressions)),
        };
    }

    /**
     * @param array<string, scalar> $searchFields
     * @param Closure $match
     * @return array<int, Andx|Orx|Func|Comparison|string>
     */
    public function createExpressions(array $searchFields, Closure $match): array
    {
        $expressions = [];
        foreach ($searchFields as $searchField => $searchValue) {
            if ($searchValue === null or (is_string($searchValue) and strlen($searchValue) === 0)) {
                continue;
            }

            $expression = $match($searchField, $searchValue);

            if ($expression !== null) {
                $expressions[] = $expression;
            }
        }

        return $expressions;
    }
}
