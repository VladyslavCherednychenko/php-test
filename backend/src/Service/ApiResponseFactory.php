<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ApiResponseFactory
{
    public function __construct(
        private SerializerInterface $serializer,
        private TranslatorInterface $translator
    ) {}

    public function success(
        string $message = '',
        mixed $data = null,
        int $statusCode = 200,
        array $groups = [],
        array $context = []
    ): JsonResponse {
        return $this->createResponse([
            'status'  => $this->translator->trans('response_factory.status_success'),
            'message' => $message,
            'data'    => $data,
        ], $statusCode, $groups, $context);
    }

    public function error(
        string $message,
        array $errors = [],
        int $statusCode = 400,
        array $context = []
    ): JsonResponse {
        return $this->createResponse([
            'status'  => $this->translator->trans('response_factory.status_error'),
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode, [], $context);
    }

    private function createResponse(
        array $payload,
        int $statusCode,
        array $groups,
        array $context
    ): JsonResponse {
        $context = array_merge($context, [
            'groups' => $groups,
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ]);

        $json = $this->serializer->serialize($payload, 'json', $context);

        return new JsonResponse($json, $statusCode, [], true);
    }
}
