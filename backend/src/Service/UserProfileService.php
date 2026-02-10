<?php

namespace App\Service;

use App\Dto\ProfileDto;
use App\Entity\UserProfile;
use App\Repository\UserProfileRepository;
use App\Constants\ImagesConstants;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(
        private UserProfileRepository $userProfileRepository,
        private UserService $userService,
        private TranslatorInterface $translator
    ) {}

    public function getProfileById(int $profile_id): ?UserProfile
    {
        if ($profile_id < 1) {
            return null;
        }
        return $this->userProfileRepository->getProfileById($profile_id);
    }

    public function getProfileByUsername(string $username): ?UserProfile
    {
        return $this->userProfileRepository->getProfileByUsername($username);
    }

    public function getUserProfile(int $user_id): ?UserProfile
    {
        $dbUser = $this->userService->getUserById($user_id);

        if (!$dbUser) {
            return null;
        }

        $result = $this->userProfileRepository->getUserProfile($user_id);

        // lazy creation of profile on first access via userId (if not exists)
        if ($result == null) {
            $profile = new UserProfile();
            $profile->setUser($dbUser);
            $profile->setUsername('user_' . bin2hex(random_bytes(6)));
            $profile->setProfileImage(ImagesConstants::PFP_DEFAULT_PATH);
            $result =  $this->userProfileRepository->createOrUpdateProfile($profile);
        }

        return $result;
    }

    public function findProfilesByUsername(string $username, int $limit = 5)
    {
        return $this->userProfileRepository->findProfilesByUsername($username, $limit);
    }

    public function createOrUpdateProfile(ProfileDto $userProfile): UserProfile
    {
        $user = $this->userService->getCurrentUser();
        $userEmail = $user->getUserIdentifier();
        $dbUser = $this->userService->getUserByEmail($userEmail);

        if ($dbUser == null) {
            throw new AccessDeniedHttpException($this->translator->trans('service.profile.create_or_update.access_denied'));
        }

        $profile = $dbUser->getProfile() ?? new UserProfile();
        $profile->setUser($dbUser);
        $profile->setUsername($userProfile->username);
        $profile->setFirstName($userProfile->firstName);
        $profile->setLastName($userProfile->lastName);
        $profile->setBio($userProfile->bio);

        if ($profile->getProfileImage() == null) {
            $profile->setProfileImage(ImagesConstants::PFP_DEFAULT_PATH);
        }

        return $this->userProfileRepository->createOrUpdateProfile($profile);
    }

    public function updateProfilePicture(string $path): UserProfile
    {
        $user = $this->userService->getCurrentUser();
        $userEmail = $user->getUserIdentifier();
        $dbUser = $this->userService->getUserByEmail($userEmail);

        if ($dbUser == null) {
            throw new AccessDeniedHttpException($this->translator->trans('service.profile.create_or_update.access_denied'));
        }

        $profile = $dbUser->getProfile() ?? new UserProfile();
        $profile->setUser($dbUser);
        $profile->setProfileImage($path);
        return $this->userProfileRepository->createOrUpdateProfile($profile);
    }

    public function deleteProfilePicture(): UserProfile
    {
        $user = $this->userService->getCurrentUser();
        $userEmail = $user->getUserIdentifier();
        $dbUser = $this->userService->getUserByEmail($userEmail);

        if ($dbUser == null) {
            throw new AccessDeniedHttpException($this->translator->trans('service.profile.create_or_update.access_denied'));
        }

        $profile = $dbUser->getProfile() ?? new UserProfile();
        $profile->setUser($dbUser);
        $profile->setProfileImage(ImagesConstants::PFP_DEFAULT_PATH);
        return $this->userProfileRepository->createOrUpdateProfile($profile);
    }
}
