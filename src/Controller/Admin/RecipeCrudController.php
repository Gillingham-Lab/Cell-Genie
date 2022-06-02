<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Form\RecipeIngredientType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('shortName'),
            TextField::new('longName'),
            TextField::new('category'),

            FormField::addPanel("Recipe"),
            NumberField::new("concentrationFactor"),
            NumberField::new("pH")
                ->setLabel("pH"),
            TextEditorField::new("comment")
                ->hideOnIndex(),
            CollectionField::new("ingredients", "Ingredients")
                ->setEntryType(RecipeIngredientType::class)
                ->setEntryIsComplex(true),
        ];
    }
}
