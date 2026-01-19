<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class DtoValidationException extends \InvalidArgumentException implements HttpExceptionInterface
{
    public function __construct(
        private array $errors,
        string $message = "Validation Failed",
        private int $statusCode = 422
    ) {
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return [];
    }
}
