<?php
declare(strict_types=1);

namespace App\Form\Storage;

use App\Entity\DoctrineEntity\Storage\Rack;
use App\Form\SaveableType;
use App\Form\User\PrivacyAwareType;
use App\Form\VisualisationType;
use App\Repository\Storage\RackRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RackType extends SaveableType
{
    public function __construct(
        private RackRepository $rackRepository,
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rackRepository = $this->rackRepository;
        $currentEntity = $builder->getData();

        $parentChoices = function() use ($rackRepository, $currentEntity) {
            if ($currentEntity->getUlid() === null) {
                $results = $rackRepository->getTree();
            } else {
                $results = $rackRepository->getTree($currentEntity);
            }

            $choices = [];
            foreach ($results as $result) {
                $label = trim($result["sort_path"]);
                $label = substr($label, 2, strlen($label)-4);
                $label = implode(' | ', explode('","', $label));

                $choices[$label] = $result[0];
            }

            return $choices;
        };

        $builder
            ->add(
                $builder->create("_general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Location data",
                ])
                ->add("name", TextType::class, [
                    "label" => "Name of the location",
                    "help" => "5-255 characters; used to identify the location. No parent names.",
                    "required" => true,
                ])
                ->add("comment", CKEditorType::class, [
                    "label" => "A comment about the location.",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ])
                ->add("pinCode", TextType::class, [
                    "label" => "Pin code",
                    "help" => "Pin-Code of the lab or another hint on how to access it.",
                    "required" => true,
                ])
                ->add("maxBoxes", IntegerType::class, [
                    "label" => "Box capacity",
                    "help" => "Maximum amount of boxes within this location, with 0 = infinite. Interesting for freezer racks, for example.",
                    "required" => false,
                ])
                ->add("parent", ChoiceType::class, [
                    "label" => "Parent location",
                    "choices" => $parentChoices(),
                    "group_by" => function(Rack $rack) { return $rack->getParent()?->getPathName(); },
                    "placeholder" => "Empty",
                    "required" => false,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership"
                ])
            )
            ->add(
                $builder->create("_visualisation", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Picture",
                ])
                ->add("visualisation", VisualisationType::class, [
                    #"inherit_data" => true,
                    "label" => "Visualisation",
                    "required" => false,
                ])
            )
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Rack::class,
        ]);

        parent::configureOptions($resolver);
    }
}