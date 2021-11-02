<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Experiment;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalMeasurement;
use App\Entity\ExperimentalRun;
use App\Entity\ExperimentalRunWell;
use App\Genie\DataSet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route("/api/public/data/runs/{experiment}", name: "api_runs")]
    #[Route("/api/public/data/runs/{experiment}/raw", name: "api_runs_raw")]
    public function allExperimentalRuns(
        Request $request,
        Experiment $experiment,
    ): Response {
        $response = new Response(null, Response::HTTP_OK);
        $response->headers->set("Content-Type", "text/plain");

        $dataset = new DataSet($experiment);

        $normalisation = true;
        if ($request->get("_route") === "api_runs_raw") {
            $normalisation = false;
        }

        $lines = $dataset->allToArray(normalise: $normalisation, includeRunDate: true);

        $response->setContent($this->linesToTSV($lines));

        return $response;
    }

    #[Route("/api/public/data/single-run/{experimentalRun}", name: "api_single_run")]
    #[Route("/api/public/data/single-run/{experimentalRun}/raw", name: "api_single_run_raw")]
    public function singleExperimentalRun(
        Request $request,
        ExperimentalRun $experimentalRun,
    ): Response {
        $response = new Response(null, Response::HTTP_OK);
        $response->headers->set("Content-Type", "text/plain");

        $experiment = $experimentalRun->getExperiment();
        $dataset = new DataSet($experiment);

        $normalisation = true;
        if ($request->get("_route") === "api_single_run_raw") {
            $normalisation = false;
        }

        $lines = $dataset->runToArray($experimentalRun, header: true, normalise: $normalisation, comments: true, includeRunDate: false);

        $response->setContent($this->linesToTSV($lines));

        return $response;
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