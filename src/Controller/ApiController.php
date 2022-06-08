<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Experiment;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalMeasurement;
use App\Entity\ExperimentalRun;
use App\Entity\ExperimentalRunWell;
use App\Entity\Recipe;
use App\Genie\DataSet;
use App\Pole\Calculator;
use App\Pole\Quantity;
use App\Pole\Unit\Amount;
use App\Pole\Unit\MassConcentration;
use App\Pole\Unit\MolarConcentration;
use App\Pole\Unit\Volume;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route("/api/public/recipe/{recipe}", name: "recipe")]
    public function oneRecipe(
        Request $request,
        Recipe $recipe,
    ): Response {
        $response = new JsonResponse(null, Response::HTTP_OK);

        $volumeDesired = floatval($request->query->get("volume", 1000.0));
        $concentrationFactor = floatval($request->query->get("concentrationFactor", $recipe->getConcentrationFactor()));

        $volumeQuantity = Volume::create($volumeDesired, "mL");

        # Add ingredients
        $ingredients = [];

        foreach ($recipe->getIngredients() as $ingredient) {
            $concentration = $ingredient->getConcentration();
            $concentrationUnit = $ingredient->getConcentrationUnit();

            if (MolarConcentration::getInstance()->supports($concentrationUnit)) {
                $concentrationQuantity = MolarConcentration::create($concentration, $concentrationUnit);
            } elseif (MassConcentration::getInstance()->supports($concentrationUnit)) {
                $concentrationQuantity = MassConcentration::create($concentration, $concentrationUnit);
            } elseif (Amount::getInstance()->supports($concentrationUnit)) {
                $concentrationQuantity = Amount::create($concentration, $concentrationUnit);
            } else {
                $concentrationQuantity = Amount::create($concentration);
            }

            $calculator = new Calculator();
            $amountQuantityForVolume = $calculator->multiply($concentrationQuantity, $volumeQuantity);

            $ingredients[] = [
                "shortName" => $ingredient->getChemical()->getShortName(),
                "longName" => $ingredient->getChemical()->getLongName(),
                "concentration" => $concentration,
                "concentrationUnit" => $concentrationUnit,
                "quantity" => $amountQuantityForVolume->getValue(),
            ];
        }

        $answer = [
            "id" => $recipe->getId(),
            "shortName" => $recipe->getShortName(),
            "longName" => $recipe->getLongName(),
            "category" => $recipe->getCategory(),
            "pH" => $recipe->getPH(),
            "concentrationFactor" => $concentrationFactor,
            "comment" => $recipe->getComment(),
            "ingredients" => $ingredients,
            "volume" => $volumeDesired,
        ];
        $response->setData($answer);

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