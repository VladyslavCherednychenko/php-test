<?php

namespace App\Helpers;

use App\Entity\UserProfile;
use Symfony\Component\HttpFoundation\Request;

class ImageHelper
{
    public static function attachHostToImage(Request $request, UserProfile $profile): void
    {
        if ($profile->getProfileImage()) {
            $profile->setProfileImage($request->getSchemeAndHttpHost() . $profile->getProfileImage());
        }
    }
}
