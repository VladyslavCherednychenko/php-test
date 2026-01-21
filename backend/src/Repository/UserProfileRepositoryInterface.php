<?php

namespace App\Repository;

use App\Entity\UserProfile;

interface UserProfileRepositoryInterface
{
    public function getProfileById(int $id);

    public function getProfileByUsername(string $query, int $limit = 5);

    public function createOrUpdateProfile(UserProfile $userProfile): UserProfile;
}
