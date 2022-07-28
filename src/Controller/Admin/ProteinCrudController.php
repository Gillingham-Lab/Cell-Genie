<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Protein;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ProteinCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Protein::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, "ROLE_ADMIN");
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel("General information"),
            IdField::new('ulid')->hideOnForm(),
            TextField::new('shortName', label: "Gene name (like MGMT)"),
            TextField::new('longName', label: "Long name"),
            UrlField::new("proteinAtlasUri", label: "URL to the protein atlas"),

            FormField::addPanel("Experimental details"),
            AssociationField::new("antibodies", label: "Antibodies used against this protein.")
                ->setFormTypeOption("by_reference", false),
        ];
    }
}
