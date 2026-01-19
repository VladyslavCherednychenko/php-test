<?php

namespace App\Controller;

use App\Service\ApiResponseFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/health', name: '_api_health_')]
class HealthCheckController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private ApiResponseFactory $responseFactory
    ) {}

    #[Route('', name: 'check', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        $isDbAlive = true;

        try {
            $connection = $this->entityManager->getConnection();
            $connection->executeQuery('SELECT 1');
        } catch (\Exception $e) {
            $isDbAlive = false;
        }

        return $this->responseFactory->create(
            message: $this->translator->trans('api.health_check.response_message'),
            data: [
                'status' => 'ok',
                'message' => $this->translator->trans('api.health_check.success'),
                'php_version' => PHP_VERSION,
                'db_available' => $isDbAlive
            ],
        );
    }
}
