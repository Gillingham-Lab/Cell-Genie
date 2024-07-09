<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\User\User;
use App\Form\User\PrivacyAwareType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalRunType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalRun::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("_general", FormType::class, [
                    "label" => "General",
                    "inherit_data" => true,
                ])
                ->add("name", TextType::class, [
                    "label" => "Experiment Run Name",
                    "required" => true,
                ])
                ->add("labjournal", TextType::class, [
                    "label" => "Lab journal entry number",
                    "required" => false,
                ])
                ->add("comment", TextareaType::class, [
                    "required" => false,
                ])
                ->add("scientist", EntityType::class, [
                    "class" => User::class,
                    "group_by" => fn(User $user) => $user->getGroup()->getShortName(),
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("ownership", PrivacyAwareType::class, [
                    "label" => "Ownership",
                    "required"  => true,
                    "inherit_data" => true,
                ])
            )
        ;
    }
}