<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CellAliquote;
use App\Entity\Traits\HasBoxTrait;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CellAliquoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CellAliquote::class;
    }

    public function createEntity(string $entityFqcn): CellAliquote
    {
        $entity = new CellAliquote();
        $entity->setCell(null);
        return $entity;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab("General"),
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new("cell")
                ->setQueryBuilder(fn (QueryBuilder $builder) => $builder->orderBy("entity.cellNumber", "ASC")),
            DateField::new('aliquoted_on')->setFormat("yyyy-MM-dd")->setRequired(false),
            AssociationField::new('aliquoted_by')->setQueryBuilder(fn (QueryBuilder $builder) => $builder->orderBy("entity.fullName", "ASC")),
            TextField::new("cryoMedium")
                ->hideOnIndex(),

            ... HasBoxTrait::crudField(),

            TextField::new("vialColor"),
            IntegerField::new("passage")
                ->setRequired(false),
            TextField::new("passageDetail")
                ->setHelp("Describe what the passage number means. Passage after getting it from ATCC, after thawing, after new cell line?"),
            IntegerField::new("cellCount")
                ->setLabel("Number of cells")
                ->setHelp("Please give the number of cells in k/mL"),
            IntegerField::new("vials"),

            FormField::addTab("Testing"),
            FormField::addPanel("Mycoplasma"),
            DateField::new("mycoplasmaTestedOn")->setFormat("yyyy-MM-dd")->setRequired(false)->hideOnIndex(),
            AssociationField::new("mycoplasmaTestedBy")->hideOnIndex(),
            ChoiceField::new("mycoplasmaResult")
                ->setTranslatableChoices([
                    "unknown" => "unknown",
                    "positive" => "positive",
                    "negative" => "negative",
                    "unclear" => "unclear",
                ]),
            TextEditorField::new("mycoplasma")
                ->setHelp("Additional information (what test; what there anything weird; references).")
                ->hideOnIndex(),

            FormField::addPanel("Other"),
            TextEditorField::new("typing")
                ->hideOnIndex(),
            TextEditorField::new("history")
                ->hideOnIndex(),
        ];
    }
}
