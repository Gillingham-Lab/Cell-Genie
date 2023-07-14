<?php
declare(strict_types=1);

namespace App\Form\Traits;

use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;

trait PrivacyLevelTrait
{
    private function addPrivacyLevelField(FormBuilderInterface $builder, Security $security): void
    {
        $entity = $builder->getData();

        if ($security->isGranted("ROLE_ADMIN") or $security->isGranted("owns", $entity)) {
            $readonly = false;
        } else {
            $readonly = true;
        }

        $builder->add("privacyLevel", EnumType::class, [
            "class" => PrivacyLevel::class,
            "disabled" => $readonly,
        ]);
    }
}