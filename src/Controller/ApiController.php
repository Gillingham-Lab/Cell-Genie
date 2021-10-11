<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Experiment;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalMeasurement;
use App\Entity\ExperimentalRun;
use App\Entity\ExperimentalRunWell;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route("/api/public/data/runs/{experiment}", name: "api_runs")]
    public function allExperimentalRuns(
        Experiment $experiment,
    ): Response {
        $response = new Response(null, Response::HTTP_OK);
        $response->headers->set("Content-Type", "text/plain");

        // Prepare response
        $lines = [
            "#Experiment: {$experiment->getName()}",
            "#Experiment ID: {$experiment->getId()}",
            "#Experiment Owner: {$experiment->getOwner()}",
            "#Created: {$experiment->getCreatedAt()->format('Y-m-d H:i:s')}",
            "#Modified: {$experiment->getModifiedAt()->format('Y-m-d H:i:s')}",
        ];

        $header = ["run id", "run name", "Nr", "Name", "ESTD"];

        /** @var ExperimentalCondition $condition */
        foreach ($experiment->getConditions() as $condition) {
            $header[] = $condition->getTitle();
        }

        /** @var ExperimentalMeasurement $measurement */
        foreach ($experiment->getMeasurements() as $measurement) {
            if ($measurement->isInternalStandard()) {
                $header[] = $measurement->getTitle() . " (ISTD)";
            } else {
                $header[] = $measurement->getTitle();
            }
        }

        $lines = [
            ... $lines,
            $header,
        ];

        foreach ($experiment->getExperimentalRuns() as $run) {
            $runLines = $this->getRunLines($run);
            $runLines = array_map(fn($x) => [$run->getId(), $run->getName(), ... $x], $runLines);

            $lines = [...$lines, ...$runLines];
        }

        $response->setContent($this->linesToTSV($lines));

        return $response;
    }

    #[Route("/api/public/data/single-run/{experimentalRun}", name: "api_single_run")]
    public function singleExperimentalRun(
        ExperimentalRun $experimentalRun,
    ): Response {
        $response = new Response(null, Response::HTTP_OK);
        $response->headers->set("Content-Type", "text/plain");

        $experiment = $experimentalRun->getExperiment();

        // Prepare response
        $lines = [
            "#Experiment: {$experiment->getName()}",
            "#Experiment ID: {$experiment->getId()}",
            "#Run name: {$experimentalRun->getName()}",
            "#Run id: {$experimentalRun->getId()}",
            "#Owner: {$experimentalRun->getOwner()}",
            "#Created: {$experimentalRun->getCreatedAt()->format('Y-m-d H:i:s')}",
            "#Modified: {$experimentalRun->getModifiedAt()->format('Y-m-d H:i:s')}",
        ];

        $header = ["Nr", "Name", "ESTD"];

        /** @var ExperimentalCondition $condition */
        foreach ($experiment->getConditions() as $condition) {
            $header[] = $condition->getTitle();
        }

        /** @var ExperimentalMeasurement $measurement */
        foreach ($experiment->getMeasurements() as $measurement) {
            if ($measurement->isInternalStandard()) {
                $header[] = $measurement->getTitle() . " (ISTD)";
            } else {
                $header[] = $measurement->getTitle();
            }
        }

        $lines = [
            ... $lines,
            $header,
            ... $this->getRunLines($experimentalRun)
        ];

        $response->setContent($this->linesToTSV($lines));

        return $response;
    }

    protected function getRunLines(ExperimentalRun $experimentalRun): array
    {
        $experiment = $experimentalRun->getExperiment();
        $experimentalRunData = $experimentalRun->getData();

        $generalConditions = [];
        $specialConditionIds = [];
        $specialMeasurementIds = [];

        /** @var ExperimentalCondition $condition */
        foreach ($experiment->getConditions() as $condition) {
            if ($condition->isGeneral()) {
                $datum = "NaN";

                if (isset($experimentalRunData["conditions"])) {
                    foreach ($experimentalRunData["conditions"] as $conditionDatum) {
                        if ($conditionDatum["id"] === $condition->getId()->toBase58()) {
                            $datum = $conditionDatum["value"];
                        }
                    }
                }

                $generalConditions[] = $datum;
            } else {
                $specialConditionIds[] = $condition->getId()->toBase58();
            }
        }

        /** @var ExperimentalMeasurement $measurement */
        foreach ($experiment->getMeasurements() as $measurement) {
            $specialMeasurementIds[] = $measurement->getId()->toBase58();
        }

        $lines = [];

        /** @var ExperimentalRunWell $well */
        foreach ($experimentalRun->getWells() as $well) {
            $conditions = [];
            $measurements = [];
            $wellData = $well->getWellData();

            foreach ($specialConditionIds as $id) {
                $datum = "NaN";

                if (isset($wellData["conditions"])) {
                    foreach ($wellData["conditions"] as $wellDatum) {
                        if ($wellDatum["id"] === $id) {
                            $datum = (string)$wellDatum["value"];
                        }
                    }
                }

                $conditions[] = $datum;
            }

            foreach ($specialMeasurementIds as $id) {
                $datum = "NaN";

                if (isset($wellData["measurements"])) {
                    foreach ($wellData["measurements"] as $wellDatum) {
                        if ($wellDatum["id"] === $id) {
                            $datum = (string)$wellDatum["value"];
                        }
                    }
                }

                $measurements[] = $datum;
            }

            $lines[] = [
                $well->getWellNumber(),
                $well->getWellName(),
                ($well->isExternalStandard() ? "yes" : "no"),
                ... $generalConditions,
                ... $conditions,
                ... $measurements,
            ];
        }

        return $lines;
    }

    protected function linesToTSV(array $lines): string
    {
        $content = "";
        /** @var array|string $line */
        foreach ($lines as $line) {
            if (is_array($line)) {
                $line = array_map(fn($x) => str_replace(["\t", "\r\n", "\n"], " ", (string)$x), $line);
                $line = implode("\t", $line);
            }

            $content .= $line . "\n";
        }

        return $content;
    }
}