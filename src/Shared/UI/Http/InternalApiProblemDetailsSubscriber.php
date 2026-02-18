<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class InternalApiProblemDetailsSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/internal/api/')) {
            return;
        }

        $exception = $event->getThrowable();

        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $detail = 'An unexpected internal error occurred.';
        if ($exception instanceof AccessDeniedHttpException || $exception instanceof AccessDeniedException) {
            $status = Response::HTTP_FORBIDDEN;
            $detail = 'You are not allowed to access this resource.';
        } elseif ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $detail = $exception->getMessage() !== '' ? $exception->getMessage() : $detail;
        }

        $traceId = $this->generateTraceId();
        $this->logger->warning('internal_api_error', [
            'trace_id' => $traceId,
            'path' => $request->getPathInfo(),
            'status' => $status,
            'exception' => $exception,
        ]);

        $event->setResponse(new JsonResponse($this->buildProblemPayload($request, $status, $detail, $traceId), $status, [
            'Content-Type' => 'application/problem+json',
        ]));
    }

    /**
     * @return array{
     *     type: string,
     *     title: string,
     *     status: int,
     *     detail: string,
     *     instance: string,
     *     traceId: string
     * }
     */
    private function buildProblemPayload(Request $request, int $status, string $detail, string $traceId): array
    {
        return [
            'type' => 'about:blank',
            'title' => Response::$statusTexts[$status] ?? 'Error',
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->getPathInfo(),
            'traceId' => $traceId,
        ];
    }

    private function generateTraceId(): string
    {
        try {
            return bin2hex(random_bytes(8));
        } catch (\Throwable) {
            return uniqid('trace_', true);
        }
    }
}
