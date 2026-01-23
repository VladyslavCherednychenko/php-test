<?php
namespace App\Service;

use App\Dto\ProfileDto;
use App\Entity\UserProfile;
use App\Repository\UserProfileRepository;
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

    public function findProfilesByUsername(string $username, int $limit = 5)
    {
        $limit = min(10, $limit);
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

        return $this->userProfileRepository->createOrUpdateProfile($profile);
    }
}
