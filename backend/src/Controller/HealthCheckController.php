<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HealthCheckController extends AbstractController
{
    #[Route(path: '/api/health', name: 'app_health', methods: ['GET'])]
    public function healthCheck(TranslatorInterface $translator): JsonResponse
    {
        $message = $translator->trans('api.health_check.success');

        return new JsonResponse([
            'status' => 'ok',
            'message' => $message,
            'php_version' => PHP_VERSION
        ]);
    }
}
