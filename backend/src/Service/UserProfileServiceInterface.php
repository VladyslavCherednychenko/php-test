<?php
namespace App\Service;

use App\Entity\UserProfile;

interface UserProfileServiceInterface
{
    public function getProfileById(int $profile_id);

    public function findProfilesByUsername(string $query, int $limit = 5);

    public function createOrUpdateProfile(UserProfile $userProfile): UserProfile;
}
