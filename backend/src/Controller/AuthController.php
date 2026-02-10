<?php

namespace App\Controller;

use App\Dto\AuthDto;
use App\Dto\UserRegistrationDto;
use App\Entity\User;
use App\Service\ApiResponseFactory;
use App\Service\AuthServiceInterface;
use App\Service\RefreshTokenServiceInterface;
use App\Service\UserServiceInterface;
use App\Service\RefreshTokenCookieServiceInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
        private RefreshTokenCookieServiceInterface $refreshTokenCookieService,
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

        $createdUser = $this->userService->createUser($user);
        $accessToken = $this->jwtManager->create($createdUser);
        $refreshToken = $this->refreshTokenService->createToken($createdUser, $dto->rememberMe);

        $response = $this->responseFactory->success(
            message: $this->translator->trans('api.auth.register.messages.created'),
            data: ['access_token' => $accessToken, 'user' => $createdUser],
            statusCode: 201,
            groups: ['user:auth']
        );

        $this->refreshTokenCookieService->attachRefreshCookie($response, $refreshToken);

        return $response;
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(#[MapRequestPayload] AuthDto $dto): JsonResponse
    {
        $user = $this->authService->getUser($dto)
            ?? throw new BadCredentialsException($this->translator->trans('api.auth.login.incorrect_credentials'));

        $accessToken = $this->jwtManager->create($user);
        $refreshToken = $this->refreshTokenService->createToken($user, $dto->rememberMe);

        $response = $this->responseFactory->success(
            message: $this->translator->trans('api.auth.login.access_granted'),
            data: ['access_token' => $accessToken, 'user' => $user],
            statusCode: 200,
            groups: ['user:auth']
        );

        $this->refreshTokenCookieService->attachRefreshCookie($response, $refreshToken);

        return $response;
    }

    #[Route('/me', name: 'get_authenticated_user_credentials', methods: ['GET'])]
    public function getAuthenticatedUserCredentials(): JsonResponse
    {
        $user = $this->userService->getCurrentUser();

        return $this->responseFactory->success(
            message: $this->translator->trans('api.auth.get_authenticated_user_credentials.message'),
            data: ['user' => $user],
            statusCode: 200,
            groups: ['user:auth']
        );
    }

    // Terminates current session of the user
    #[Route('/token/terminate/current', name: 'terminate_current_session', methods: ['POST'])]
    public function terminate(Request $request): JsonResponse
    {
        $refreshToken = $this->refreshTokenCookieService->getRefreshTokenFromRequest($request);
        $this->refreshTokenService->deleteToken($refreshToken);

        $response = $this->responseFactory->success(statusCode: 204);
        $this->refreshTokenCookieService->clearSessionCookiesFromResponse($response);

        return $response;
    }

    // Terminates all sessions of the current user
    #[Route('/token/terminate/all', name: 'terminate_all_sessions_for_user', methods: ['POST'])]
    public function terminateAll(Request $request): JsonResponse
    {
        $refreshToken = $this->refreshTokenCookieService->getRefreshTokenFromRequest($request);
        $user = $refreshToken->getUser();

        $this->refreshTokenService->deleteAllTokensFromUser($user);

        $response = $this->responseFactory->success(statusCode: 204);
        $this->refreshTokenCookieService->clearSessionCookiesFromResponse($response);

        return $response;
    }

    #[Route('/token/refresh', name: 'token_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $this->refreshTokenCookieService->getRefreshTokenFromRequest($request);
        $user = $refreshToken->getUser();

        $newAccessToken = $this->jwtManager->create($user);
        $newRefreshToken = $this->refreshTokenService->rotateToken($refreshToken);

        $response = $this->responseFactory->success(
            message: $this->translator->trans('api.auth.token_refresh.success'),
            data: [
                'access_token' => $newAccessToken,
                'user' => $user,
            ],
            groups: ['user:auth']
        );

        $this->refreshTokenCookieService->attachRefreshCookie($response, $newRefreshToken);

        return $response;
    }
}
