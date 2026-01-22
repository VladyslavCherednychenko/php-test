<?php
namespace App\Controller;

use App\Service\ApiResponseFactory;
use App\Service\UserProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/profiles', name: '_api_profiles_')]
class ProfileController extends AbstractController
{
    public function __construct(
        private UserProfileService $userProfileService,
        private ApiResponseFactory $responseFactory
    ) {}

    #[Route('/{id}', name: 'find_by_id', methods: ['GET'])]
    public function getProfileById(Request $request): JsonResponse
    {
        throw new \Exception('Not Implemented');
    }

    #[Route('/', name: 'find_profiles_by_username', methods: ['GET'])]
    public function findProfilesByUsername(Request $request): JsonResponse
    {
        throw new \Exception('Not Implemented');
    }

    #[Route('/', name: 'create_or_update_profile', methods: ['POST'])]
    public function createOrUpdateProfile(Request $request): JsonResponse
    {
        throw new \Exception('Not Implemented');
    }
}
