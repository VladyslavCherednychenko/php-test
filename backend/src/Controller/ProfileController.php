<?php

namespace App\Controller;

use App\Dto\ProfileDto;
use App\Service\ApiResponseFactory;
use App\Service\ImageStorageServiceInterface;
use App\Service\UserProfileServiceInterface;
use App\Helper\ImageHelper;
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

    #[Route('/{id<\d+>}', name: 'find_by_profile_id', methods: ['GET'])]
    public function getProfileById(int $id, Request $request): JsonResponse
    {
        $result = $this->profileService->getProfileById($id);
        if (! $result) {
            return $this->responseFactory->error($this->translator->trans('api.profiles.common_responses.not_found'), statusCode: 404);
        }

        ImageHelper::attachHostToImage($request, $result);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.common_responses.found'),
            data: ['profile' => $result],
            groups: ['profile:read']
        );
    }

    #[Route('/username/{username}', name: 'find_by_profile_username', methods: ['GET'])]
    public function getProfileByUsername(string $username, Request $request): JsonResponse
    {
        $result = $this->profileService->getProfileByUsername($username);
        if (! $result) {
            return $this->responseFactory->error($this->translator->trans('api.profiles.common_responses.not_found'), statusCode: 404);
        }

        ImageHelper::attachHostToImage($request, $result);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.common_responses.found'),
            data: ['profile' => $result],
            groups: ['profile:read']
        );
    }

    #[Route('/user/{id<\d+>}', name: 'find_by_user_id', methods: ['GET'])]
    public function getUserProfile(int $id, Request $request): JsonResponse
    {
        $result = $this->profileService->getUserProfile($id);
        if (! $result) {
            return $this->responseFactory->error($this->translator->trans('api.profiles.common_responses.not_found'), statusCode: 404);
        }

        ImageHelper::attachHostToImage($request, $result);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.common_responses.found'),
            data: ['profile' => $result],
            groups: ['profile:read']
        );
    }

    #[Route('/search', name: 'find_profiles_by_username', methods: ['GET'])]
    public function findProfilesByUsername(
        Request $request,
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

        foreach ($profiles as $result) {
            ImageHelper::attachHostToImage($request, $result);
        }

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.find_profiles_by_username.result.message'),
            data: ['profiles' => $profiles],
            groups: ['profile:read']
        );
    }

    #[Route('', name: 'create_profile', methods: ['POST'])]
    public function createProfile(#[MapRequestPayload] ProfileDto $profile, Request $request): JsonResponse
    {
        $result = $this->profileService->createOrUpdateProfile($profile);
        ImageHelper::attachHostToImage($request, $result);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.create_profile.message'),
            data: ['profile' => $result],
            groups: ['profile:read'],
            statusCode: 201
        );
    }

    #[Route('', name: 'update_profile', methods: ['PUT'])]
    public function updateProfile(#[MapRequestPayload] ProfileDto $profile, Request $request): JsonResponse
    {
        $result = $this->profileService->createOrUpdateProfile($profile);
        ImageHelper::attachHostToImage($request, $result);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.update_profile.message'),
            data: ['profile' => $result],
            groups: ['profile:read']
        );
    }

    #[Route('/picture', name: 'upload_profile_picture', methods: ['POST'])]
    public function uploadProfilePicture(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->responseFactory->error(
                $this->translator->trans('api.profiles.picture.no_file.message'),
                errors: ['error' => $this->translator->trans('api.profiles.picture.no_file')],
                statusCode: 400
            );
        }

        $path = $this->imageStorageService->saveImage($file);
        $result = $this->profileService->updateProfilePicture($path);
        ImageHelper::attachHostToImage($request, $result);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.upload_profile_picture.message'),
            data: ['profile' => $result],
            groups: ['profile:read'],
            statusCode: 201
        );
    }

    #[Route('/picture', name: 'delete_profile_picture', methods: ['DELETE'])]
    public function deleteProfilePicture(Request $request): JsonResponse
    {
        $result = $this->profileService->deleteProfilePicture();
        ImageHelper::attachHostToImage($request, $result);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.profiles.delete_profile_picture.message'),
            data: ['profile' => $result],
            groups: ['profile:read'],
            statusCode: 200
        );
    }
}
