<?php

namespace App\EventListener;

use App\Service\ApiResponseFactory;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener]
class ExceptionListener
{
    public function __construct(
        private ApiResponseFactory $responseFactory,
        private TranslatorInterface $translator
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $statusCode = ($exception instanceof HttpExceptionInterface) ? $exception->getStatusCode() : 500;

        $message = $this->translator->trans('exception_listener.error_message.generic');
        $errors = ['error' => $exception->getMessage()];
        $debug = null;

        if (
            $exception instanceof UnprocessableEntityHttpException &&
            $exception->getPrevious() instanceof ValidationFailedException
        ) {

            $validationException = $exception->getPrevious();
            $violations = $validationException->getViolations();

            $violationsList = [];
            foreach ($violations as $violation) {
                $violationsList[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            $message = $this->translator->trans('exception_listener.error_message.validation');
            $errors = $violationsList;
        }

        if ($_ENV['APP_ENV'] === 'dev') {
            $debug = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }

        $event->setResponse(
            $this->responseFactory->create($message, [], $errors, $statusCode, $debug)
        );
    }
}
