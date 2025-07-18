<?php
declare(strict_types=1);

namespace App\Form\User;

use App\Entity\DoctrineEntity\User\User;
use App\Form\SaveableType;
use App\Security\UserRole;
use App\Security\Voter\User\UserVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<User>
 */
class UserType extends SaveableType
{
    public function __construct(
        private Security $security,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $entity */
        $entity = $builder->getData();

        $builder
            ->add(
                $builder
                    ->create("__personal", FormType::class, options: [
                        "inherit_data" => true,
                        "data_class" => User::class,
                        "error_bubbling" => true,
                        "label" => "Personal settings",
                    ])
                    ->add("email", options: [
                        "disabled" => !$this->security->isGranted(UserVoter::ATTR_CHANGE_IDENTITY, $entity),
                    ])
                    ->add("plainPassword", RepeatedType::class, options: [
                        "type" => PasswordType::class,
                        "invalid_message" => "The passwords must match",
                        "disabled" => !$this->security->isGranted(UserVoter::ATTR_CHANGE_PASSWORD, $entity),
                        "required" => $options["require_password"],
                        "first_options" => ["label" => "Password"],
                        "second_options" => ["label" => "Repeat password"],
                    ])
                    ->add("personalAddress")
                    ->add("title")
                    ->add("firstName", options: [
                        "disabled" => !$this->security->isGranted(UserVoter::ATTR_CHANGE_IDENTITY, $entity),
                    ])
                    ->add("lastName", options: [
                        "disabled" => !$this->security->isGranted(UserVoter::ATTR_CHANGE_IDENTITY, $entity),
                    ])
                    ->add("suffix")
                    ->add("orcid", options: [
                        "label" => "ORCID",
                    ])
                    ->add("phoneNumber")
                    ->add("office")
                    ->add("group", options: [
                        "disabled" => !$this->security->isGranted(UserVoter::ATTR_CHANGE_GROUP, $entity),
                    ])
                    ->add("roles", ChoiceType::class, options: [
                        "choices" => UserRole::getChoices($this->security),
                        "disabled" => !$this->security->isGranted(UserVoter::ATTR_CHANGE_IDENTITY, $entity),
                        "multiple" => true,
                        "required" => false,
                        "empty_data" => [],
                    ])
                    ->add("isActive", options: [
                        "disabled" => !($this->security->isGranted(UserVoter::ATTR_CHANGE_IDENTITY, $entity) and !($this->security->getUser() === $entity)),
                    ])
                    ->add("isAdmin", options: [
                        "disabled" => !($this->security->isGranted("ROLE_ADMIN") and !($this->security->getUser() === $entity)),
                    ]),
            )
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => User::class,
            "require_password" => false,
        ]);

        $resolver->setAllowedTypes("require_password", "bool");

        parent::configureOptions($resolver);
    }
}
