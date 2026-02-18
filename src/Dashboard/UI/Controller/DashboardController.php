<?php

declare(strict_types=1);

namespace App\Dashboard\UI\Controller;

use App\Dashboard\Application\UseCase\AddWidget\AddWidgetCommand;
use App\Dashboard\Application\UseCase\AddWidget\AddWidgetHandler;
use App\Dashboard\Application\UseCase\ConfigureWidget\ConfigureWidgetCommand;
use App\Dashboard\Application\UseCase\ConfigureWidget\ConfigureWidgetHandler;
use App\Dashboard\Application\UseCase\RemoveWidget\RemoveWidgetCommand;
use App\Dashboard\Application\UseCase\RemoveWidget\RemoveWidgetHandler;
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
    private const array ALLOWED_WIDGET_TYPES = [
        'product_search',
        'nutriscore_distribution',
        'additives_overview',
    ];

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
        if (!$this->isCsrfTokenValid('dashboard_reorder', (string) $request->request->get('_token', ''))) {
            $this->addFlash('auth_error', 'Invalid CSRF token.');

            return $this->redirectToRoute('dashboard_home');
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

    #[Route('/dashboard/widget/add', name: 'dashboard_widget_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addWidget(
        Request $request,
        AddWidgetHandler $addWidget,
        Security $security,
    ): Response {
        $user = $security->getUser();
        if (!$user instanceof AppUser) {
            throw $this->createAccessDeniedException('Authenticated user expected.');
        }

        $type = trim((string) $request->request->get('type', ''));
        if (!\in_array($type, self::ALLOWED_WIDGET_TYPES, true)) {
            $this->addFlash('auth_error', 'Unknown widget type.');

            return $this->redirectToRoute('dashboard_home');
        }
        if (!$this->isCsrfTokenValid('dashboard_widget_add', (string) $request->request->get('_token', ''))) {
            $this->addFlash('auth_error', 'Invalid CSRF token.');

            return $this->redirectToRoute('dashboard_home');
        }

        $addWidget->handle(new AddWidgetCommand(
            ownerId: $user->id(),
            widgetType: $type,
        ));

        return $this->redirectToRoute('dashboard_home');
    }

    #[Route('/dashboard/widget/{widgetId}/remove', name: 'dashboard_widget_remove', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function removeWidget(
        string $widgetId,
        Request $request,
        RemoveWidgetHandler $removeWidget,
        Security $security,
    ): Response {
        $user = $security->getUser();
        if (!$user instanceof AppUser) {
            throw $this->createAccessDeniedException('Authenticated user expected.');
        }
        if (!$this->isCsrfTokenValid('dashboard_widget_remove_'.$widgetId, (string) $request->request->get('_token', ''))) {
            $this->addFlash('auth_error', 'Invalid CSRF token.');

            return $this->redirectToRoute('dashboard_home');
        }

        $removeWidget->handle(new RemoveWidgetCommand(
            ownerId: $user->id(),
            widgetId: $widgetId,
        ));

        return $this->redirectToRoute('dashboard_home');
    }

    #[Route('/dashboard/widget/{widgetId}/configure', name: 'dashboard_widget_configure', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function configureWidget(
        string $widgetId,
        Request $request,
        ConfigureWidgetHandler $configureWidget,
        Security $security,
    ): Response {
        $user = $security->getUser();
        if (!$user instanceof AppUser) {
            throw $this->createAccessDeniedException('Authenticated user expected.');
        }
        if (!$this->isCsrfTokenValid('dashboard_widget_configure_'.$widgetId, (string) $request->request->get('_token', ''))) {
            $this->addFlash('auth_error', 'Invalid CSRF token.');

            return $this->redirectToRoute('dashboard_home');
        }

        $query = trim((string) $request->request->get('query', ''));
        $configuration = $query !== '' ? ['query' => $query] : [];

        $configureWidget->handle(new ConfigureWidgetCommand(
            ownerId: $user->id(),
            widgetId: $widgetId,
            configuration: $configuration,
        ));

        return $this->redirectToRoute('dashboard_home');
    }
}
