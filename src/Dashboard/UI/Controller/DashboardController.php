<?php

declare(strict_types=1);

namespace App\Dashboard\UI\Controller;

use App\Dashboard\Application\UseCase\ReorderWidgets\ReorderWidgetsCommand;
use App\Dashboard\Application\UseCase\ReorderWidgets\ReorderWidgetsHandler;
use App\IdentityAccess\Infrastructure\Security\AppUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->redirectToRoute('dashboard_home');
    }

    #[Route('/dashboard', name: 'dashboard_home', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function dashboard(): Response
    {
        return $this->render('dashboard/home.html.twig');
    }

    #[Route('/dashboard/reorder', name: 'dashboard_reorder', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reorder(
        Request $request,
        ReorderWidgetsHandler $reorderWidgets,
        Security $security,
    ): Response {
        $user = $security->getUser();
        if (!$user instanceof AppUser) {
            throw $this->createAccessDeniedException('Authenticated user expected.');
        }

        $orderedIds = array_values(array_filter(
            array_map('trim', explode(',', (string) $request->request->get('ordered_ids', ''))),
            static fn (string $value): bool => $value !== '',
        ));

        if ($orderedIds !== []) {
            $reorderWidgets->handle(new ReorderWidgetsCommand($user->id(), $orderedIds));
        }

        return $this->redirectToRoute('dashboard_home');
    }
}
