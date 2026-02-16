<?php

declare(strict_types=1);

namespace App\IdentityAccess\UI\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class TwoFactorEnforcementSubscriber implements EventSubscriberInterface
{
    /**
     * @param list<string> $allowedRoutesBeforeSecondFactor
     */
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UrlGeneratorInterface $urlGenerator,
        private array $allowedRoutesBeforeSecondFactor = [
            'identity_access_login',
            'identity_access_2fa',
            '_profiler_home',
            '_profiler_search',
            '_profiler_search_bar',
            '_profiler_phpinfo',
            '_profiler_xdebug',
            '_profiler_search_results',
            '_profiler_open_file',
            '_profiler',
            '_wdt',
            '_security_logout',
        ],
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token === null || !\is_object($token->getUser())) {
            return;
        }

        $request = $event->getRequest();
        $route = (string) $request->attributes->get('_route');
        if (\in_array($route, $this->allowedRoutesBeforeSecondFactor, true)) {
            return;
        }

        $session = $request->getSession();
        $isVerified = (bool) $session->get('identity_access.pending_2fa.verified', false);
        if ($isVerified) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('identity_access_2fa')));
    }
}

