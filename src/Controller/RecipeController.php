<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    public function __construct(
        readonly private RecipeRepository $recipeRepository,
    ) {

    }

    #[Route("/recipes", name: "app_recipes")]
    public function compounds(): Response
    {
        $recipes = $this->recipeRepository->findBy([], orderBy: ["category" => "ASC", "shortName" => "ASC"]);

        return $this->render("parts/recipes/recipes.html.twig", [
            "recipes" => $recipes
        ]);
    }

    #[Route("/recipes/view/{recipeId}", name: "app_recipe_view")]
    public function viewRecipe($recipeId): Response
    {
        $recipe = $this->recipeRepository->find($recipeId);

        if (!$recipe) {
            $this->addFlash("error", "Recipe {$recipeId} was not found.");
            return $this->redirect("app_recipes", status: Response::HTTP_NOT_FOUND);
        }

        return $this->render("parts/recipes/recipe.html.twig", [
            "recipe" => $recipe,
        ]);
    }
}