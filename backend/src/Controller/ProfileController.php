<?php

namespace App\Controller;

use App\Dto\ProfileDto;
use App\Service\ApiResponseFactory;
use App\Service\ImageStorageServiceInterface;
use App\Service\UserProfileServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/profiles', name: 'api_profiles_')]
class ProfileController extends AbstractController
{
    public function __construct(
        private UserProfileServiceInterface $profileService,
        private ImageStorageServiceInterface $imageStorageService,
        private ApiResponseFactory $responseFactory,
        private TranslatorInterface $translator
    ) {}

    #[Route('/{id<\d+>}', name: 'find_by_id', methods: ['GET'])]
    public function getProfileById(int $id): JsonResponse
    {
        $profile = $this->profileService->getProfileById($id);
        if (! $profile) {
            return $this->responseFactory->error($this->translator->trans('api.profiles.find_by_id.not_found'), statusCode: 404);
        }

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.find_by_id.found'),
            data: ['profile' => $profile],
            groups: ['user:read']
        );
    }

    #[Route('/search', name: 'find_profiles_by_username', methods: ['GET'])]
    public function findProfilesByUsername(
        #[MapQueryParameter] string $username,
        #[MapQueryParameter(options: [1, 10])] int $limit = 10
    ): JsonResponse {
        if (! $username || $username == '') {
            return $this->responseFactory->error(
                $this->translator->trans('api.profiles.find_profiles_by_username.validation.message'),
                errors: ['username' => $this->translator->trans('api.profiles.find_profiles_by_username.validation.username_is_empty')]
            );
        }

        if ($limit < 1 || $limit > 10) {
            return $this->responseFactory->error(
                $this->translator->trans('api.profiles.find_profiles_by_username.validation.message'),
                errors: ['username' => $this->translator->trans('api.profiles.find_profiles_by_username.validation.limit_between_1_and_10')]
            );
        }

        $profiles = $this->profileService->findProfilesByUsername($username, $limit);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.find_profiles_by_username.result.message'),
            data: ['profiles' => $profiles],
            groups: ['user:read']
        );
    }

    #[Route('', name: 'create_profile', methods: ['POST'])]
    public function createProfile(#[MapRequestPayload] ProfileDto $profile): JsonResponse
    {
        $result = $this->profileService->createOrUpdateProfile($profile);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.create_profile.message'),
            data: ['profile' => $result],
            groups: ['user:read'],
            statusCode: 201
        );
    }

    #[Route('/', name: 'update_profile', methods: ['PUT'])]
    public function updateProfile(#[MapRequestPayload] ProfileDto $profile): JsonResponse
    {
        $result = $this->profileService->createOrUpdateProfile($profile);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.update_profile.message'),
            data: ['profile' => $result],
            groups: ['user:read']
        );
    }

    #[Route('/picture', name: 'upload_profile_picture', methods: ['POST'])]
    public function uploadProfilePicture(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'no file'], 400);
        }

        $path = $this->imageStorageService->saveImage($file);

        return new JsonResponse(['path' => $path], 201);
    }
}
