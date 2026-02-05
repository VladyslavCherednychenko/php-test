<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\RefreshToken;

interface RefreshTokenCookieServiceInterface
{
    public function attachRefreshCookie(JsonResponse $response, RefreshToken $refreshToken): void;

    public function getRefreshTokenFromRequest(Request $request): RefreshToken;

    public function clearSessionCookiesFromResponse(JsonResponse $response): void;
}
