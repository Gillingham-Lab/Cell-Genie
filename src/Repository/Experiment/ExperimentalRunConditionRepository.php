<?php declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Genie\Codec\ExperimentValueCodec;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\FormRowTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<ExperimentalRunCondition>
 */
class ExperimentalRunConditionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($registry, ExperimentalRunCondition::class);
    }

    /**
     * @param ExperimentalRunCondition $condition
     * @return ExperimentalRunCondition[]
     */
    public function getReferenceConditions(ExperimentalRunCondition $condition): array
    {
        $run = $condition->getExperimentalRun();
        $fields = $run?->getDesign()?->getFields();

        $topFields = [];
        $conditionFields = [];

        foreach ($fields as $field) {
            if ($field->isReferenced() === false or $field->getRole() === ExperimentalFieldRole::Datum or $field->getRole() === ExperimentalFieldRole::Comparison) {
                continue;
            }

            if ($field->getRole() === ExperimentalFieldRole::Top) {
                $topFields[] = $field;
            } else {
                $conditionFields[] = $field;
            }
        }

        // Query runs with matching conditions first
        $query = $this->createQueryBuilder("c")
            ->addSelect("run")
            ->addSelect("cm")
            ->leftJoin("c.experimentalRun", "run")
            ->leftJoin("c.models", "cm")
            ->where("run.design = :experimentalDesign")
            ->setParameter("experimentalDesign", $run->getDesign()->getId())
            ->orderBy("run.name", "ASC")
            ->addOrderBy("c.name", "ASC")
        ;

        $cacheKey = "";

        $c = 0;
        foreach ($topFields as $field) {
            $datumName = $field->getFormRow()->getFieldName();

            if ($run->getData()->containsKey($datumName) === false) {
                continue;
            }

            $codec = new ExperimentValueCodec($run->getDatum($datumName)->getType());

            $query = $query
                ->leftJoin("run.data", "data$c")
                ->andWhere("data$c.name = :field$c")
                ->setParameter("field$c", $datumName);


            if ($field->getReferenceValue() !== null) {
                $value = $field->getReferenceValue();
                $notValue = $run->getDatum($datumName)->getValue();
            } else {
                $value = $run->getDatum($datumName)->getValue();
                $notValue = null;
            }

            if ($notValue !== null) {
                $cacheKey .= "&{$datumName}=" . $codec->encode($notValue);
                $cacheKey .= "&{$datumName}!=" . $codec->encode($value);
            } else {
                $cacheKey .= "&{$datumName}=" . $codec->encode($value);
            }

            if ($field->getFormRow()->getType() === FormRowTypeEnum::EntityType) {
                $query = $query->andWhere("data$c.referenceUuid = :value$c");
                $value = $value[0];

                if ($notValue !== null) {
                    $query = $query->andWhere("data$c.referenceUuid != :notValue$c");
                    $notValue = $notValue[0];
                }
            } elseif (in_array($field->getFormRow()->getType(), [FormRowTypeEnum::TextType, FormRowTypeEnum::TextAreaType])) {
                $query = $query->andWhere("lower(convert_from(data$c.value, 'UTF-8')) = lower(:value$c)");

                if ($notValue !== null) {
                    $query = $query->andWhere("lower(convert_from(data$c.value, 'UTF-8')) != lower(:notValue$c)");
                }
            } else {
                $query = $query->andWhere("data$c.value = decode(:value$c, 'hex')");
                $value = bin2hex($codec->encode($value));

                if ($notValue !== null) {
                    $notValue = bin2hex($codec->encode($notValue));
                    $query = $query->andWhere("data$c.value != decode(:notValue$c, 'hex')");
                }
            }

            $query = $query->setParameter("value$c", $value);
            if ($notValue !== null) {
                $query = $query->setParameter("notValue$c", $notValue);
            }

            $c++;
        }

        foreach ($conditionFields as $field) {
            $datumName = $field->getFormRow()->getFieldName();

            if ($condition->getData()->containsKey($datumName) === false) {
                continue;
            }

            $codec = new ExperimentValueCodec($condition->getDatum($datumName)->getType());

            $query = $query
                ->leftJoin("c.data", "data$c")
                ->andWhere("data$c.name = :field$c")
                ->setParameter("field$c", $datumName);

            if ($field->getReferenceValue() !== null) {
                $value = $field->getReferenceValue();
                $notValue = $condition->getDatum($datumName)->getValue();
            } else {
                $value = $condition->getDatum($datumName)->getValue();
                $notValue = null;
            }

            if ($notValue !== null) {
                $cacheKey .= "&{$datumName}=" . $codec->encode($notValue);
                $cacheKey .= "&{$datumName}!=" . $codec->encode($value);
            } else {
                $cacheKey .= "&{$datumName}=" . $codec->encode($value);
            }

            if ($field->getFormRow()->getType() === FormRowTypeEnum::EntityType) {
                $query = $query->andWhere("data$c.referenceUuid = :value$c");
                $value = $value[0];

                if ($notValue !== null) {
                    $query = $query->andWhere("data$c.referenceUuid != :notValue$c");
                    $notValue = $notValue[0];
                }
            } elseif (in_array($field->getFormRow()->getType(), [FormRowTypeEnum::TextType, FormRowTypeEnum::TextAreaType])) {
                $query = $query->andWhere("lower(convert_from(data$c.value, 'UTF-8')) = lower(:value$c)");

                if ($notValue !== null) {
                    $query = $query->andWhere("lower(convert_from(data$c.value, 'UTF-8')) != lower(:notValue$c)");
                }
            } else {
                $query = $query->andWhere("data$c.value = decode(:value$c, 'hex')");
                $value = bin2hex($codec->encode($value));

                if ($notValue !== null) {
                    $notValue = bin2hex($codec->encode($notValue));
                    $query = $query->andWhere("data$c.value != decode(:notValue$c, 'hex')");
                }
            }

            $query = $query->setParameter("value$c", $value);
            if ($notValue !== null) {
                $query = $query->setParameter("notValue$c", $notValue);
            }

            $c++;
        }

        $this->logger->debug("Accessing cache: $cacheKey");

        return $query->getQuery()->getResult();
    }
}
