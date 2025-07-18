<?php
declare(strict_types=1);

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

class SQLiteForeignKeyEnforcer implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::preFlush,
        ];
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
        $platform = $args->getObjectManager()->getConnection()->getDatabasePlatform();

        if ($platform instanceof SqlitePlatform) {
            $args->getObjectManager()->getConnection()->executeQuery("PRAGMA foreign_keys = ON");
        }
    }
}
