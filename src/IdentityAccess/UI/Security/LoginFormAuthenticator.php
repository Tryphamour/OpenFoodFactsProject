<?php

declare(strict_types=1);

namespace App\IdentityAccess\UI\Security;

use App\IdentityAccess\Application\Port\SecurityUserRepository;
use App\IdentityAccess\Application\UseCase\IssueSecondFactorChallenge\IssueSecondFactorChallengeCommand;
use App\IdentityAccess\Application\UseCase\IssueSecondFactorChallenge\IssueSecondFactorChallengeHandler;
use App\IdentityAccess\Application\UseCase\RecordFailedLoginAttempt\RecordFailedLoginAttemptCommand;
use App\IdentityAccess\Application\UseCase\RecordFailedLoginAttempt\RecordFailedLoginAttemptHandler;
use App\IdentityAccess\Infrastructure\Security\AppUser;
use App\IdentityAccess\Infrastructure\Security\AppUserProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class LoginFormAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly AppUserProvider $userProvider,
        private readonly SecurityUserRepository $securityUsers,
        private readonly RecordFailedLoginAttemptHandler $recordFailedLoginAttempt,
        private readonly IssueSecondFactorChallengeHandler $issueSecondFactorChallenge,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod(Request::METHOD_POST) && $request->attributes->get('_route') === 'identity_access_login';
    }

    public function authenticate(Request $request): Passport
    {
        $email = strtolower(trim((string) $request->request->get('email', '')));
        $password = (string) $request->request->get('password', '');
        $now = new \DateTimeImmutable();

        if ($email === '' || $password === '') {
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

        $user = $this->userProvider->loadUserByIdentifier($email);
        if (!$user instanceof AppUser) {
            throw new CustomUserMessageAuthenticationException('Invalid user type.');
        }

        $securityUser = $this->securityUsers->getById($user->id());
        if ($securityUser->isLockedAt($now)) {
            throw new CustomUserMessageAuthenticationException('Account is temporarily locked. Try again later.');
        }

        if (!password_verify($password, $user->getPassword())) {
            $this->recordFailedLoginAttempt->handle(new RecordFailedLoginAttemptCommand($user->id(), $now));
            $securityUser = $this->securityUsers->getById($user->id());

            if ($securityUser->isLockedAt($now)) {
                throw new CustomUserMessageAuthenticationException('Account locked after too many failed attempts.');
            }

            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

        return new SelfValidatingPassport(new UserBadge($email, fn (): AppUser => $user));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if (!$user instanceof AppUser) {
            throw new \RuntimeException('Unexpected authenticated user.');
        }

        $challengeId = $this->issueSecondFactorChallenge->handle(
            new IssueSecondFactorChallengeCommand($user->id(), $user->getUserIdentifier()),
        );

        $session = $request->getSession();
        $session->set('identity_access.pending_2fa.challenge_id', $challengeId);
        $session->set('identity_access.pending_2fa.user_id', $user->id());
        $session->set('identity_access.pending_2fa.verified', false);

        return new RedirectResponse($this->urlGenerator->generate('identity_access_2fa'));
    }

    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('auth_error', $exception->getMessageKey());

        return new RedirectResponse($this->urlGenerator->generate('identity_access_login'));
    }
}
