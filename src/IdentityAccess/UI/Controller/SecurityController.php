<?php

declare(strict_types=1);

namespace App\IdentityAccess\UI\Controller;

use App\IdentityAccess\Application\UseCase\RecordSuccessfulLogin\RecordSuccessfulLoginCommand;
use App\IdentityAccess\Application\UseCase\RecordSuccessfulLogin\RecordSuccessfulLoginHandler;
use App\IdentityAccess\Application\UseCase\VerifySecondFactorChallenge\VerifySecondFactorChallengeCommand;
use App\IdentityAccess\Application\UseCase\VerifySecondFactorChallenge\VerifySecondFactorChallengeHandler;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallengeAlreadyVerified;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallengeExpired;
use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallengeTooManyAttempts;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'identity_access_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        return $this->render('identity_access/security/login.html.twig', [
            'last_email' => (string) $request->request->get('email', ''),
        ]);
    }

    #[Route('/2fa', name: 'identity_access_2fa', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function twoFactor(
        Request $request,
        VerifySecondFactorChallengeHandler $verifySecondFactorChallenge,
        RecordSuccessfulLoginHandler $recordSuccessfulLogin,
        #[Autowire(service: 'limiter.identity_access_2fa_verify')] RateLimiterFactory $limiterFactory,
    ): Response {
        $session = $request->getSession();
        $challengeId = (string) $session->get('identity_access.pending_2fa.challenge_id', '');
        $userId = (string) $session->get('identity_access.pending_2fa.user_id', '');

        if ($challengeId === '' || $userId === '') {
            $this->addFlash('auth_error', '2FA session not initialized. Please login again.');

            return $this->redirectToRoute('identity_access_login');
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $limiter = $limiterFactory->create($userId);
            $limit = $limiter->consume(1);
            if (!$limit->isAccepted()) {
                $this->addFlash('auth_error', 'Too many 2FA attempts. Please wait and retry.');

                return $this->render('identity_access/security/two_factor.html.twig');
            }

            $submittedCode = trim((string) $request->request->get('code', ''));
            try {
                $ok = $verifySecondFactorChallenge->handle(
                    new VerifySecondFactorChallengeCommand($challengeId, $submittedCode),
                );

                if ($ok) {
                    $session->set('identity_access.pending_2fa.verified', true);
                    $session->remove('identity_access.pending_2fa.challenge_id');
                    $recordSuccessfulLogin->handle(new RecordSuccessfulLoginCommand($userId));

                    return $this->redirectToRoute('dashboard_home');
                }

                $this->addFlash('auth_error', 'Invalid verification code.');
            } catch (SecondFactorChallengeExpired|SecondFactorChallengeTooManyAttempts|SecondFactorChallengeAlreadyVerified) {
                $this->addFlash('auth_error', '2FA challenge is no longer valid. Please login again.');

                return $this->redirectToRoute('identity_access_login');
            }
        }

        return $this->render('identity_access/security/two_factor.html.twig');
    }

    #[Route('/logout', name: 'identity_access_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('Handled by Symfony security firewall logout.');
    }
}

