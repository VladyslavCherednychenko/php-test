<?php
namespace App\Controller;

use App\Dto\ProfileDto;
use App\Service\ApiResponseFactory;
use App\Service\UserProfileService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/profiles', name: '_api_profiles_')]
class ProfileController extends AbstractController
{
    public function __construct(
        private UserProfileService $userProfileService,
        private UserService $userService,
        private ApiResponseFactory $responseFactory,
        private TranslatorInterface $translator
    ) {}

    #[Route('/{id}', name: 'find_by_id', methods: ['GET'])]
    public function getProfileById(int $id): JsonResponse
    {
        $profile = $this->userProfileService->getProfileById($id);
        if ($profile == null) {
            return $this->responseFactory->create($this->translator->trans('api.profile_conroller.find_by_id.not_found'), statusCode: 404);
        }

        return $this->responseFactory->create(
            message: $this->translator->trans('api.profile_conroller.find_by_id.found'),
            data: ['profile' => $profile],
            context: ['groups' => 'user:read']);
    }

    #[Route('/', name: 'find_profiles_by_username', methods: ['GET'])]
    public function findProfilesByUsername(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;
        $limit = $data['limit'] ?? 5;

        if ($username === null) {
            return $this->responseFactory->create(
                $this->translator->trans('api.profile_conroller.find_profiles_by_username.validation.message'),
                errors: ['username' => $this->translator->trans('api.profile_conroller.find_profiles_by_username.validation.username_is_empty')],
                statusCode: 400);
        }
        if ($limit < 1) {
            return $this->responseFactory->create(
                $this->translator->trans('api.profile_conroller.find_profiles_by_username.validation.message'),
                errors: ['username' => $this->translator->trans('api.profile_conroller.find_profiles_by_username.validation.limit_less_than_one')],
                statusCode: 400);
        }

        $profiles = $this->userProfileService->findProfilesByUsername($username, $limit);
        return $this->responseFactory->create(
            message: $this->translator->trans('api.profile_conroller.find_profiles_by_username.result.message'),
            data: [
                'profiles' => $profiles,
            ],
            context: ['groups' => 'user:read']);
    }

    #[Route('/', name: 'create_or_update_profile', methods: ['POST'])]
    public function createOrUpdateProfile(#[MapRequestPayload] ProfileDto $profile): JsonResponse
    {
        $result = $this->userProfileService->createOrUpdateProfile($profile);

        // is it ok to never return a 'created' status?
        return $this->responseFactory->create(
            message: $this->translator->trans('api.profile_conroller.create_or_update_profile.update.message'),
            data: ['profile' => $result],
            context: ['groups' => 'user:read']);
    }
}
