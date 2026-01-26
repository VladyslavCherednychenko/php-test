<?php

namespace App\Controller;

use App\Dto\AuthDto;
use App\Dto\UserRegistrationDto;
use App\Entity\User;
use App\Service\ApiResponseFactory;
use App\Service\AuthServiceInterface;
use App\Service\RefreshTokenServiceInterface;
use App\Service\UserServiceInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
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

            return $this->responseFactory->create(
                message: $this->translator->trans('api.auth.register.messages.failed'),
                errors: $messages,
                statusCode: 400
            );
        }

        try {
            $newUser = $this->userService->createUser($user);

            $accessToken = $this->jwtManager->create($newUser);
            $refreshToken = $this->refreshTokenService->createToken($newUser);

            $response = $this->responseFactory->create(
                message: $this->translator->trans('api.auth.register.messages.created'),
                data: [
                    'access_token' => $accessToken,
                    'user' => $newUser,
                ],
                statusCode: 201,
                context: ['groups' => 'user:read']
            );

            $response->headers->setCookie(
                Cookie::create('REFRESH_TOKEN')
                    ->withValue($refreshToken->getToken())
                    ->withExpires($refreshToken->getExpiresAt())
                    ->withHttpOnly(true)
                    ->withSecure(true)
                    ->withSameSite(Cookie::SAMESITE_STRICT)
                    ->withPath('/api/auth/token/refresh')
            );

            return $response;
        } catch (\Exception $e) {
            return $this->responseFactory->create(
                message: $this->translator->trans('api.auth.register.messages.failed'),
                errors: [
                    'error' => $this->translator->trans('api.auth.register.errors.generic.value'),
                ],
                statusCode: 409
            );
        }
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(#[MapRequestPayload] AuthDto $dto): JsonResponse
    {
        if (! $dto) {
            return $this->responseFactory->create(
                $this->translator->trans('api.auth.login.access_denied'),
                errors: [
                    'credentials' => $this->translator->trans('api.auth.login.invalid_credentials'),
                ],
                statusCode: 401
            );
        }

        $user = $this->authService->getUser($dto);

        if (! $user) {
            return $this->responseFactory->create(
                $this->translator->trans('api.auth.login.access_denied'),
                errors: [
                    'credentials' => $this->translator->trans('api.auth.login.incorrect_credentials'),
                ],
                statusCode: 401
            );
        }

        $accessToken = $this->jwtManager->create($user);
        $refreshToken = $this->refreshTokenService->createToken($user);

        $response = $this->responseFactory->create(
            message: $this->translator->trans('api.auth.login.access_granted'),
            data: [
                'access_token' => $accessToken,
                'user' => $user,
            ],
            context: ['groups' => 'user:read']
        );

        $response->headers->setCookie(
            Cookie::create('REFRESH_TOKEN')
                ->withValue($refreshToken->getToken())
                ->withExpires($refreshToken->getExpiresAt())
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withSameSite(Cookie::SAMESITE_STRICT)
                ->withPath('/api/auth/token/refresh')
        );

        return $response;
    }

    #[Route('/token/refresh', name: 'token_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $tokenString = $request->cookies->get('REFRESH_TOKEN');

        if (! $tokenString) {
            return $this->responseFactory->create(
                message: $this->translator->trans('api.auth.token_refresh.missing'),
                statusCode: 401
            );
        }

        $refreshToken = $this->refreshTokenService->findValidToken($tokenString);

        if (! $refreshToken) {
            return $this->responseFactory->create(
                message: $this->translator->trans('api.auth.token_refresh.expired'),
                statusCode: 401
            );
        }

        $user = $refreshToken->getUser();
        $newAccessToken = $this->jwtManager->create($user);
        $newRefreshToken = $this->refreshTokenService->rotateToken($refreshToken);

        $response = $this->responseFactory->create(
            message: $this->translator->trans('api.auth.token_refresh.success'),
            data: [
                'access_token' => $newAccessToken,
                'user' => $user,
            ],
            context: ['groups' => 'user:read']
        );

        $response->headers->setCookie(
            Cookie::create('REFRESH_TOKEN')
                ->withValue($newRefreshToken->getToken())
                ->withExpires($newRefreshToken->getExpiresAt())
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withSameSite(Cookie::SAMESITE_STRICT)
                ->withPath('/api/auth/token/refresh')
        );

        return $response;
    }
}
