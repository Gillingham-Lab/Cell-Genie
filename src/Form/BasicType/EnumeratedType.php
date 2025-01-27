<?php
declare(strict_types=1);

namespace App\Form\BasicType;

use App\Entity\DoctrineEntity\User\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<string>
 */
class EnumeratedType extends AbstractType
{
    public readonly User $user;

    public function __construct(
        Security $security,
    ) {
        /** @var User $user */
        $user = $security->getUser();

        $this->user = $user;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define("enumeration_type")
            ->allowedTypes("string")
            ->required()
            ->allowedValues("cell", "cell_culture", "antibody", "chemical", "oligo", "protein", "plasmid")
        ;

        $resolver
            ->define("enumeration_url")
            ->allowedTypes("null", "string")
            ->default(null)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars["enumeration_type"] = $options["enumeration_type"];
        $view->vars["enumeration_url"] = $options["enumeration_url"];
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}