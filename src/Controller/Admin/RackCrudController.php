<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\DoctrineEntity\Storage\Rack;
use App\Repository\Storage\RackRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Rack>
 */
class RackCrudController extends AbstractCrudController
{
    public function __construct(
        private RackRepository $rackRepository,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Rack::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        $repository = $this->rackRepository;

        return [
            IdField::new('ulid')
                ->hideOnForm(),
            TextField::new('name')->setHelp("Do not use comma in the name."),
            AssociationField::new("parent")
                ->hideOnForm(),

            ChoiceField::new("parent")
                ->onlyOnForms()
                ->setChoices(function () use ($repository, $currentEntity) {
                    $results = $repository->getTree($currentEntity);

                    $choices = [];
                    foreach ($results as $result) {
                        $label = trim($result["sort_path"]);
                        $label = substr($label, 2, strlen($label) - 4);
                        $label = implode(' | ', explode('","', $label));

                        $choices[$label] = $result[0];
                    }

                    return $choices;
                })
            ,
            /*->setCustomOption("choices", )
                ->setCustomOption("choice_label", function(Rack $rack) {
                    return $rack->getPathName();
                })
                ->setCustomOption("group_by", function(Rack $rack) {
                    return $rack->getParent();
                }),*/
            #AssociationField::new("parent")->setHelp("Try not to cause a looped structure; There are currently no safety measures in place."),
            IntegerField::new("maxBoxes"),
            AssociationField::new("children")->onlyOnIndex(),
            AssociationField::new("boxes")->onlyOnIndex(),
        ];
    }
}
