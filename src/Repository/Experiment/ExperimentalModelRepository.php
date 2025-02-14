<?php
declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Service\CacheKeyService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @extends ServiceEntityRepository<ExperimentalModel>
 */
class ExperimentalModelRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TagAwareCacheInterface $cache,
        private readonly CacheKeyService $keyService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($registry, ExperimentalModel::class);
    }

    /**
     * @return ExperimentalModel[]
     */
    public function getModelsForConditions(string $model, ExperimentalRunCondition ... $conditions): array
    {
        if (count($conditions) === 0) {
            return [];
        }

        $cacheKey = $this->keyService->getCacheKeyFromCollection($conditions, "ExperimentalModelRepository.ModelsForConditions");

        $this->logger->debug("Cache key: $cacheKey");

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($model, $conditions) {
            $item->expiresAfter(300);

            return $this->_getModelsForConditions($model, ... $conditions);
        });
    }

    /**
     * @return ExperimentalModel[]
     */
    private function _getModelsForConditions(string $model, ExperimentalRunCondition ... $conditions): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb2 = $this->getEntityManager()->createQueryBuilder();

        $subQuery = $qb2
            ->select("cem.id")
            ->from(ExperimentalRunCondition::class, "cond")
            ->leftJoin("cond.models", "cem")
            ->where("cem.model = :model")
            ->andWhere("cond.id IN (:conditions)")
            ->getDQL();

        return $this->createQueryBuilder('em')
            ->select("em")
            ->addSelect("emp")
            ->where(
                $qb->expr()->in("em.id", $subQuery)
            )
            ->leftJoin("em.parent", "emp")
            ->setParameter("conditions", array_map(fn (ExperimentalRunCondition $c) => $c->getId(), $conditions))
            ->setParameter("model", $model)
            ->getQuery()
            ->getResult()
        ;
    }
}