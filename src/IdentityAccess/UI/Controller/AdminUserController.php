<?php

declare(strict_types=1);

namespace App\IdentityAccess\UI\Controller;

use App\IdentityAccess\Application\UseCase\CreateUser\CreateUserCommand;
use App\IdentityAccess\Application\UseCase\CreateUser\CreateUserHandler;
use App\IdentityAccess\Application\UseCase\CreateUser\InvalidUserData;
use App\IdentityAccess\Application\UseCase\CreateUser\UserEmailAlreadyExists;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class AdminUserController extends AbstractController
{
    #[Route('/new', name: 'identity_access_admin_user_new', methods: ['GET', 'POST'])]
    public function newUser(Request $request, CreateUserHandler $createUser): Response
    {
        $formData = [
            'email' => '',
            'is_admin' => false,
        ];

        if ($request->isMethod(Request::METHOD_POST)) {
            if (!$this->isCsrfTokenValid('admin_user_create', (string) $request->request->get('_token', ''))) {
                $this->addFlash('auth_error', 'Invalid CSRF token.');

                return $this->redirectToRoute('identity_access_admin_user_new');
            }

            $email = trim((string) $request->request->get('email', ''));
            $plainPassword = (string) $request->request->get('password', '');
            $isAdmin = (bool) $request->request->get('is_admin', false);
            $formData = [
                'email' => $email,
                'is_admin' => $isAdmin,
            ];

            try {
                $createUser->handle(new CreateUserCommand(
                    email: $email,
                    plainPassword: $plainPassword,
                    isAdmin: $isAdmin,
                ));

                $this->addFlash('auth_success', 'User account created.');

                return $this->redirectToRoute('identity_access_admin_user_new');
            } catch (InvalidUserData|UserEmailAlreadyExists $exception) {
                $this->addFlash('auth_error', $exception->getMessage());
            }
        }

        return $this->render('identity_access/admin/new_user.html.twig', [
            'form_data' => $formData,
        ]);
    }
}

