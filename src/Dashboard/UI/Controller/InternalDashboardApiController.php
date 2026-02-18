<?php

declare(strict_types=1);

namespace App\Dashboard\UI\Controller;

use App\Dashboard\Application\UseCase\GetDashboard\GetDashboardHandler;
use App\Dashboard\Application\UseCase\GetDashboard\GetDashboardQuery;
use App\Dashboard\UI\Api\DashboardApiResponseMapper;
use App\Dashboard\UI\Security\DashboardOwnerVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class InternalDashboardApiController extends AbstractController
{
    #[Route('/internal/api/dashboard/{ownerId}', name: 'internal_api_dashboard_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(
        string $ownerId,
        GetDashboardHandler $getDashboard,
        DashboardApiResponseMapper $responseMapper,
    ): Response {
        $ownerId = trim($ownerId);
        if ($ownerId === '') {
            throw new BadRequestHttpException('ownerId must be provided.');
        }

        $this->denyAccessUnlessGranted(DashboardOwnerVoter::VIEW, $ownerId);

        $dashboard = $getDashboard->handle(new GetDashboardQuery($ownerId));

        return new JsonResponse([
            'data' => $responseMapper->map($dashboard),
        ], Response::HTTP_OK);
    }
}
