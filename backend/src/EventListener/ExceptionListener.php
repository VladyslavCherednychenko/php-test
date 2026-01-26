<?php

namespace App\EventListener;

use App\Service\ApiResponseFactory;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

        $message = $this->translator->trans('exception_listener.message.generic');
        $errors = ['error' => $exception->getMessage()];
        $debug = null;

        if ($this->isValidationFailedException($exception)) {

            $validationException = $exception->getPrevious();
            $violations = $validationException->getViolations();

            $violationsList = [];
            foreach ($violations as $violation) {
                $violationsList[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            $message = $this->translator->trans('exception_listener.message.validation');
            $errors = $violationsList;
        }

        if ($this->isJWTEncodeFailureException($exception)) {
            $message = $this->translator->trans('exception_listener.message.jwt_encode');
            $errors = ['error' => $this->translator->trans('exception_listener.message.jwt_encode_error')];
            $statusCode = 500;
        }

        if ($this->isBadRequestHttpException($exception)) {
            $message = $this->translator->trans('exception_listener.message.bad_request');
            $errors = ['error' => $exception->getMessage()];
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

    private function isValidationFailedException(\Exception $exception): bool
    {
        return $exception instanceof UnprocessableEntityHttpException &&
            $exception->getPrevious() instanceof ValidationFailedException;
    }

    private function isJWTEncodeFailureException(\Exception $exception): bool
    {
        return $exception instanceof JWTEncodeFailureException;
    }

    private function isBadRequestHttpException(\Exception $exception): bool
    {
        return $exception instanceof BadRequestHttpException;
    }
}
