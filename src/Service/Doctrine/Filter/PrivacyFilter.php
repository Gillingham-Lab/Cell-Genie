<?php
declare(strict_types=1);

namespace App\Service\Doctrine\Filter;

use App\Entity\Interface\PrivacyAwareInterface;
use App\Genie\Enums\PrivacyLevel;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class PrivacyFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        // Check if the entity implements the PrivacyAwareInterface interface
        if (!$targetEntity->reflClass->implementsInterface(PrivacyAwareInterface::class)) {
            return "";
        }

        $ownerField = "{$targetTableAlias}.owner_id";
        $groupField = "{$targetTableAlias}.group_id";
        $privacyField = "{$targetTableAlias}.privacy_level";

        $current_user = $this->getParameter("current_user");
        $current_group = $this->getParameter("current_group");

        $privacyLevelPrivate = PrivacyLevel::Private->value;
        $privacyLevelGroup = PrivacyLevel::Group->value;
        $privacyLevelPublic = PrivacyLevel::Public->value;

        $groupFilter = strlen($current_group) > 10 ? "OR {$groupField} = $current_group" : "";

        return "(
            {$privacyField} = {$privacyLevelPublic}
        ) OR (
            {$privacyField} = {$privacyLevelGroup} AND (
                {$groupField} IS NULL $groupFilter
            )
        ) OR (
            {$privacyField} = {$privacyLevelPrivate} AND (
                {$ownerField} IS NULL OR {$ownerField} = $current_user
            )
        )";
    }
}
