<?php
namespace App\Service;

use App\Entity\UserProfile;
use App\Repository\UserProfileRepository;

class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(
        private UserProfileRepository $userProfileRepository
    ) {}

    public function getProfileById(int $profile_id)
    {
        throw new \Exception('Not Implemented');
    }

    public function findProfilesByUsername(string $query, int $limit = 5)
    {
        throw new \Exception('Not Implemented');
    }

    public function createOrUpdateProfile(UserProfile $userProfile): UserProfile
    {
        throw new \Exception('Not Implemented');
    }
}
