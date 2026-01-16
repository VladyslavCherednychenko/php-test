<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/health', name: '_api_health_')]
class HealthCheckController extends AbstractController
{
    #[Route('', name: 'check', methods: ['GET'])]
    public function healthCheck(TranslatorInterface $translator, EntityManagerInterface $entityManager): JsonResponse
    {
        $isDbAlive = true;
        $message = $translator->trans('api.health_check.success');

        try {
            $connection = $entityManager->getConnection();
            $connection->executeQuery('SELECT 1');
        } catch (\Exception $e) {
            $isDbAlive = false;
        }

        return new JsonResponse([
            'status' => 'ok',
            'message' => $message,
            'php_version' => PHP_VERSION,
            'db_available' => $isDbAlive
        ]);
    }
}
