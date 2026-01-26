<?php

namespace App\Controller;

use App\Service\ApiResponseFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/health', name: 'api_health_')]
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
        $db_message = $this->translator->trans('api.health_check.db_online');

        try {
            $connection = $this->entityManager->getConnection();
            $connection->executeQuery('SELECT 1');
        } catch (\Exception $e) {
            $isDbAlive = $this->translator->trans('api.health_check.db_offline');
        }

        return $this->responseFactory->create(
            message: $this->translator->trans('api.health_check.response_message'),
            data: [
                'status' => 'ok',
                'message' => $this->translator->trans('api.health_check.success'),
                'php_version' => PHP_VERSION,
                'DB' => $isDbAlive,
            ],
        );
    }
}
