<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Rack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rack|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rack|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rack[]    findAll()
 * @method Rack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rack::class);
    }

    public function findAllWithBoxes()
    {
        return $this->createQueryBuilder("r")
            ->select("r")
            ->addSelect("rc")
            ->addSelect("b")
            ->leftJoin("r.boxes", "b")
            ->leftJoin("r.children", "rc")
            ->orderBy("r.name")
            ->addOrderBy("b.name")
            ->groupBy("r")
            ->addGroupBy("b")
            ->addGroupBy("rc")
            ->getQuery()
            ->getResult()
            ;
    }

    public function getTree(?Rack $exludeRack = null)
    {
        $entityManager = $this->getEntityManager();

        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addEntityResult(Rack::class, 'u');
        $rsm->addFieldResult('u', 'ulid', 'ulid');
        $rsm->addFieldResult('u', 'name', 'name');
        $rsm->addScalarResult("depth", "depth", "integer");
        $rsm->addScalarResult("sort_path", "sort_path");
        $rsm->addScalarResult("path", "path");
        $rsm->addScalarResult("cycle", "cycle");

        if ($exludeRack) {
            $query = $this->getEntityManager()->createNativeQuery(
                <<<'SQL'
    WITH RECURSIVE search_graph(ulid, parent_id, name, depth, sort_path, path, cycle) AS (
    SELECT r1.ulid, r1.parent_id, r1.name, 1, ARRAY[r1.name]::varchar[], ARRAY[r1.ulid], false
    FROM rack r1 WHERE r1.parent_id IS NULL
    UNION ALL
    SELECT r2.ulid, r2.parent_id, r2.name, sg.depth + 1, (sort_path || r2.name)::varchar(255)[], path || r2.ulid, r2.ulid = ANY(path)
    FROM rack r2, search_graph sg
    WHERE r2.parent_id = sg.ulid AND NOT cycle
) SELECT DISTINCT * FROM search_graph sg2 WHERE NOT(:param = ANY(path)) ORDER BY sort_path, name;
SQL, $rsm);
            $query->setParameter("param", $exludeRack->getUlid(), "ulid");
        } else {
            $query = $this->getEntityManager()->createNativeQuery(
                <<<'SQL'
    WITH RECURSIVE search_graph(ulid, parent_id, name, depth, sort_path, path, cycle) AS (
    SELECT r1.ulid, r1.parent_id, r1.name, 1, ARRAY[r1.name]::varchar[], ARRAY[r1.ulid], false
    FROM rack r1 WHERE r1.parent_id IS NULL
    UNION ALL
    SELECT r2.ulid, r2.parent_id, r2.name, sg.depth + 1, (sort_path || r2.name)::varchar(255)[], path || r2.ulid, r2.ulid = ANY(path)
    FROM rack r2, search_graph sg
    WHERE r2.parent_id = sg.ulid AND NOT cycle
) SELECT DISTINCT * FROM search_graph sg2 ORDER BY sort_path, name;
SQL, $rsm);
        }

        $result = $query->getResult();

        foreach ($result as $res) {
            /** @var Rack $entity */
            $entity = $res[0];

            $entity->setDepth($res["depth"]);
            $entity->setUlidTree(explode('","', substr($res["path"], 2, strlen($res["path"])-4)));
            $entity->setNameTree(explode('","', substr($res["sort_path"], 2, strlen($res["sort_path"])-4)));
        }

        return $result;
    }
}
