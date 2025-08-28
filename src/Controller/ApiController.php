<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Recipe;
use App\Entity\DoctrineEntity\RecipeIngredient;
use App\Genie\Pole\Calculator;
use App\Genie\Pole\Quantity;
use App\Genie\Pole\Unit\Amount;
use App\Genie\Pole\Unit\MassConcentration;
use App\Genie\Pole\Unit\MolarAmount;
use App\Genie\Pole\Unit\MolarConcentration;
use App\Genie\Pole\Unit\MolarMass;
use App\Genie\Pole\Unit\Volume;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route("/api/public/recipe/{recipe}", name: "recipe")]
    public function oneRecipe(
        Request $request,
        Recipe $recipe,
    ): Response {
        $response = new JsonResponse(null, Response::HTTP_OK);

        if ($request->isMethod("get")) {
            $volumeDesired = floatval($request->query->get("volume", "1000.0"));
            $concentrationFactor = floatval($request->query->get("concentrationFactor", (string) $recipe->getConcentrationFactor()));
        } else {
            $content = json_decode($request->getContent(), true);

            if ($content) {
                $volumeDesired = is_null($content["volume"]) ? 1000.0 : floatval($content["volume"]);
                $concentrationFactor = is_null($content["concentrationFactor"]) ? $recipe->getConcentrationFactor() : floatval($content["concentrationFactor"]);
            } else {
                throw new Exception("Content is empty. JSON request was probably malformed or empty.");
            }
        }

        $volumeQuantity = Volume::create($volumeDesired, "mL");

        $adjustedConcentrationFactor = $concentrationFactor / $recipe->getConcentrationFactor();

        # Add ingredients
        $ingredients = [];

        /** @var RecipeIngredient $ingredient */
        foreach ($recipe->getIngredients() as $ingredient) {
            $chemical = $ingredient->getChemical();
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

            // Calculate quantity for the desired volume
            $amountQuantityForVolume = $calculator->multiply($concentrationQuantity, $volumeQuantity);
            // Adjust quantity for the desired concentration factor
            $amountQuantityForVolume = $calculator->multiply($amountQuantityForVolume, $adjustedConcentrationFactor);

            $densityQuantity = null;
            $chemicalVolumeQuantity = null;

            // If a molar mass is given and the calculated amount is a molar amount, we convert the value to mass instead.
            if ($amountQuantityForVolume->isUnit(MolarAmount::class) and $chemical->getMolecularMass() > 1) {
                $molarMass = MolarMass::create($chemical->getMolecularMass(), "g/mol");
                $amountQuantityForVolume = $calculator->multiply($amountQuantityForVolume, $molarMass);

                // If we have a mass and the chemical has a density, we also want to convert mass to volume
                // (technically, a mass concentration is a density)
                if ($chemical->getDensity() > 0) {
                    $densityQuantity = MassConcentration::create($chemical->getDensity(), "g/mL");
                    $chemicalVolumeQuantity = $calculator->divide($amountQuantityForVolume, $densityQuantity);
                }
            }

            $ingredients[] = [
                "id" => $ingredient->getId()->toRfc4122(),
                "shortName" => $chemical->getShortName(),
                "longName" => $chemical->getLongName(),
                "concentration" => $concentration,
                "concentrationUnit" => $concentrationUnit,
                "quantity" => $amountQuantityForVolume->getValue(),
                "quantity_unit" => $amountQuantityForVolume->getUnit()->getBaseUnitSymbol(),
                "quantity_formatted" => $amountQuantityForVolume->format(4, Quantity::FORMAT_ADJUST_UNIT),
                "volume" => $chemicalVolumeQuantity?->getValue(),
                "volume_unit" => $chemicalVolumeQuantity?->getUnit()->getBaseUnitSymbol(),
                "volume_formatted" => $chemicalVolumeQuantity?->format(4, Quantity::FORMAT_ADJUST_UNIT),
            ];
        }

        $answer = [
            "id" => $recipe->getId()->toRfc4122(),
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

    /**
     * @param string[]|string[][] $lines
     * @return string
     */
    protected function linesToTSV(array $lines): string
    {
        $content = "";
        /** @var string[]|string $line */
        foreach ($lines as $line) {
            if (is_array($line)) {
                $line = array_map(fn($x) => str_replace(["\t", "\r\n", "\n"], " ", (string) $x), $line);
                $line = implode("\t", $line);
            }

            $content .= $line . "\n";
        }

        return $content;
    }
}
