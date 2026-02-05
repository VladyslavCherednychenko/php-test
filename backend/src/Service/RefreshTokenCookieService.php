<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\RefreshToken;
use App\Constants\RefreshTokenConstants;

class RefreshTokenCookieService implements RefreshTokenCookieServiceInterface
{
    public function __construct(
        private RefreshTokenServiceInterface $refreshTokenService,
        private TranslatorInterface $translator
    ) {}

    public function attachRefreshCookie(JsonResponse $response, RefreshToken $refreshToken): void
    {
        $response->headers->setCookie(
            Cookie::create(RefreshTokenConstants::COOKIE_NAME)
                ->withValue($refreshToken->getToken())
                ->withExpires($refreshToken->getExpiresAt())
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withSameSite(Cookie::SAMESITE_STRICT)
                ->withPath(RefreshTokenConstants::COOKIE_PATH)
        );
    }

    public function getRefreshTokenFromRequest(Request $request): RefreshToken
    {
        $tokenString = $request->cookies->get('REFRESH_TOKEN')
            ?? throw new UnauthorizedHttpException('Bearer', $this->translator->trans('api.auth.token_refresh.missing'));

        return $this->refreshTokenService->findValidToken($tokenString)
            ?? throw new UnauthorizedHttpException('Bearer', $this->translator->trans('api.auth.token_refresh.expired'));
    }

    public function clearSessionCookiesFromResponse(JsonResponse $response): void
    {
        $response->headers->clearCookie(
            RefreshTokenConstants::COOKIE_NAME,
            RefreshTokenConstants::COOKIE_PATH
        );
    }
}
