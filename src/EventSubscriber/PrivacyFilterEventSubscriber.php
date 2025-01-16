<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\DoctrineEntity\User\User;
use App\Service\Doctrine\Filter\PrivacyFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PrivacyFilterEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        /** @var ?User $user */
        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }

        $user_id = $user->getId()->toRfc4122();

        $group = $user->getGroup();
        $group_id = $group?->getId()?->toRfc4122();

        // Configure
        $this->entityManager->getConfiguration()->addFilter("group_filter", PrivacyFilter::class);
        $filter = $this->entityManager->getFilters()->enable("group_filter");
        $filter->setParameter("current_user", $user_id);
        $filter->setParameter("current_group", $group_id);
    }
}