<?php

namespace App\Repository;

use App\Entity\UserProfile;

interface UserProfileRepositoryInterface
{
    public function getProfileById(int $profile_id);

    public function getUserProfile(int $user_id): ?UserProfile;

    public function findProfilesByUsername(string $username, int $limit = 5);

    public function createOrUpdateProfile(UserProfile $userProfile): UserProfile;
}
