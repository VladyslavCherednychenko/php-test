<?php

namespace App\Service;

use App\Dto\ProfileDto;
use App\Entity\UserProfile;

interface UserProfileServiceInterface
{
    public function getProfileById(int $profile_id): ?UserProfile;

    public function findProfilesByUsername(string $username, int $limit = 5);

    public function createOrUpdateProfile(ProfileDto $userProfile): UserProfile;

    public function updateProfilePicture(string $path): UserProfile;

    public function deleteProfilePicture(): UserProfile;
}
