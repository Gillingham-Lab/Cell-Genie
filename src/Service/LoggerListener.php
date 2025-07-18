<?php
declare(strict_types=1);
// from https://github.com/stof/StofDoctrineExtensionsBundle/blob/main/src/EventListener/LoggerListener.php
// MIT license

namespace App\Service;

use Gedmo\Loggable\Loggable;
use Gedmo\Loggable\LoggableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Sets the username from the security context by listening on kernel.request
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
readonly class LoggerListener implements EventSubscriberInterface
{
    /**
     * @param LoggableListener<Loggable> $loggableListener
     */
    public function __construct(
        private LoggableListener $loggableListener,
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => "onKernelRequest",
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MAIN_REQUEST) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if ($token !== null and $this->authorizationChecker->isGranted("IS_AUTHENTICATED_REMEMBERED")) {
            $this->loggableListener->setUsername($token->getUserIdentifier());
        }
    }
}
