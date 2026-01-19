<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ApiResponseFactory
{
    public function __construct(
        private SerializerInterface $serializer
    ) {}

    public function create(
        string $message = '',
        mixed $data = [],
        array $errors = [],
        int $statusCode = 200,
        array $context = [],
        ?array $debug = null
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
        ];

        if ($debug !== null) {
            $payload['debug'] = $debug;
        }

        $json = $this->serializer->serialize($payload, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $statusCode, [], true);
    }
}