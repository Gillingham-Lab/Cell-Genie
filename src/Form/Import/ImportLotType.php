<?php
declare(strict_types=1);

namespace App\Form\Import;

use App\Entity\Box;
use App\Form\User\PrivacyAwareType;
use App\Genie\Enums\Availability;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportLotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->
            add("number", TextType::class, [

            ])
            ->add("lotNumber", TextType::class, [

            ])
            ->add("comment", TextareaType::class, [
                "required" => false,
            ])
            ->add("availability", EnumType::class, [
                "class" => Availability::class,
            ])
            ->add("boughtOn", DateType::class, [
                "widget" => "single_text",
                "label" => "Bought on (or made on)",
            ])
            ->add("_privacy", PrivacyAwareType::class, [
                "label" => "Privacy",
                "inherit_data" => true,
                "required" => false,
            ])
            ->add("box", EntityType::class, [
                "required" => true,
                "class" => Box::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("b")
                        ->addOrderBy("b.name", "ASC");
                },
                "group_by" => function(Box $choice, $key, $value) {
                    return ($choice->getRack());
                },
            ])
            ->add("boxCoordinate", TextType::class, options: [
                "label" => "Position in box",
                "help" => "Give the position in the box. Use letters for row, and numbers for column (A12 is the first row, 12th column; AA1 is the 27th row, 1st column)",
                "required" => false,
            ])
            ->add("amount", TextType::class, options: [
                "label" => "Amount",
                "help" => "Write down the amount with a unit",
                "required" => true,
            ])
            ->add("purity", TextType::class, options: [
                "label" => "Concentration",
                "help" => "Write down the concentration with a unit. If not a solution, write 'neat' instead.",
                "required" => true,
            ])
            ->add("numberOfAliquotes", IntegerType::class, options: [
                "label" => "Number of Aliquots",
                "required" => false,
            ])
            ->add("maxNumberOfAliquots", IntegerType::class, options: [
                "label" => "Max Number of Aliquots",
                "required" => false,
            ])
            ->add("aliquoteSize", TextType::class, options: [
                "label" => "Size of each aliquot",
                "required" => false,
            ])
        ;

        parent::buildForm($builder, $options);
    }
}