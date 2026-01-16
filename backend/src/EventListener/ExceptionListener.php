<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = ($exception instanceof HttpException) ? $exception->getStatusCode() : 500;

        $responseData = [
            'status'  => 'error',
            'message' => 'An internal error occurred.',
        ];
        
        $environment = $_ENV['APP_ENV'];

        if ($environment === 'dev') {
            $responseData['debug'] = [
                'message' => $exception->getMessage(),
                'internal_code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        $event->setResponse(new JsonResponse($responseData, $statusCode));
    }
}
