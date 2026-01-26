<?php

namespace App\Controller;

use App\Dto\AuthDto;
use App\Dto\UserRegistrationDto;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Service\ApiResponseFactory;
use App\Service\AuthServiceInterface;
use App\Service\RefreshTokenServiceInterface;
use App\Service\UserServiceInterface;
use App\Constants\RefreshTokenConstants;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenServiceInterface $refreshTokenService,
        private AuthServiceInterface $authService,
        private UserServiceInterface $userService,
        private ValidatorInterface $validator,
        private ObjectMapperInterface $mapper,
        private ApiResponseFactory $responseFactory,
        private TranslatorInterface $translator
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(#[MapRequestPayload] UserRegistrationDto $dto): JsonResponse
    {
        $user = $this->mapper->map($dto, User::class);

        $errors = $this->validator->validate($user);
        if (\count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->responseFactory->error(
                message: $this->translator->trans('api.auth.register.messages.failed'),
                errors: $messages,
                statusCode: 400
            );
        }

        return $this->createAuthTokensAndResponse(
            $this->userService->createUser($user),
            $this->translator->trans('api.auth.register.messages.created'),
            201
        );
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(#[MapRequestPayload] AuthDto $dto): JsonResponse
    {
        $user = $this->authService->getUser($dto)
            ?? throw new BadCredentialsException($this->translator->trans('api.auth.login.incorrect_credentials'));

        return $this->createAuthTokensAndResponse(
            $user,
            $this->translator->trans('api.auth.login.access_granted')
        );
    }

    // Terminates current session of the user
    #[Route('/token/terminate/current', name: 'terminate_current_session', methods: ['POST'])]
    public function terminate(Request $request): JsonResponse
    {
        $refreshToken = $this->getRefreshTokenFromRequest($request);
        $this->refreshTokenService->deleteToken($refreshToken);

        return $this->createResponseAndTerminateSession();
    }

    // Terminates all sessions of the current user
    #[Route('/token/terminate/all', name: 'terminate_current_all_user_sessions', methods: ['POST'])]
    public function terminateAll(Request $request): JsonResponse
    {
        $refreshToken = $this->getRefreshTokenFromRequest($request);
        $user = $refreshToken->getUser();

        $this->refreshTokenService->deleteAllTokensFromUser($user);

        return $this->createResponseAndTerminateSession();
    }

    #[Route('/token/refresh', name: 'token_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $this->getRefreshTokenFromRequest($request);
        $user = $refreshToken->getUser();

        $newAccessToken = $this->jwtManager->create($user);
        $newRefreshToken = $this->refreshTokenService->rotateToken($refreshToken);

        $response = $this->responseFactory->success(
            message: $this->translator->trans('api.auth.token_refresh.success'),
            data: [
                'access_token' => $newAccessToken,
                'user' => $user,
            ],
            groups: ['user:read']
        );

        $this->attachRefreshCookie($response, $newRefreshToken);

        return $response;
    }

    private function createAuthTokensAndResponse(
        User $user,
        string $message,
        int $status = 200
    ): JsonResponse {
        $accessToken = $this->jwtManager->create($user);
        $refreshToken = $this->refreshTokenService->createToken($user);

        $response = $this->responseFactory->success(
            message: $message,
            data: ['access_token' => $accessToken, 'user' => $user],
            statusCode: $status,
            groups: ['user:read']
        );

        $this->attachRefreshCookie($response, $refreshToken);

        return $response;
    }

    private function attachRefreshCookie(JsonResponse $response, RefreshToken $refreshToken): void
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

    private function getRefreshTokenFromRequest(Request $request): RefreshToken
    {
        $tokenString = $request->cookies->get('REFRESH_TOKEN')
            ?? throw new UnauthorizedHttpException('Bearer', $this->translator->trans('api.auth.token_refresh.missing'));

        return $this->refreshTokenService->findValidToken($tokenString)
            ?? throw new UnauthorizedHttpException('Bearer', $this->translator->trans('api.auth.token_refresh.expired'));
    }

    private function clearSessionCookies(JsonResponse $response): void
    {
        $response->headers->clearCookie(
            RefreshTokenConstants::COOKIE_NAME,
            RefreshTokenConstants::COOKIE_PATH
        );
    }

    private function createResponseAndTerminateSession(): JsonResponse
    {
        $response = $this->responseFactory->success(statusCode: 204);

        $this->clearSessionCookies($response);

        return $response;
    }
}
