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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/auth', name: 'auth_')]
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
                message: $this->translator->trans('api.user_conroller.registration.messages.failed'),
                errors: $messages,
                statusCode: 400
            );
        }

        try {
            $newUser = $this->userService->createUser($user);

            // $accessToken = $this->jwtManager->create($user);
            // $refreshToken = $this->refreshTokenService->createToken($user);

            return $this->responseFactory->create(
                message: $this->translator->trans('api.user_conroller.registration.messages.created'),
                data: [
                    // 'access_token' => $accessToken,
                    // 'refresh_token' => $refreshToken->getToken(),
                    'user' => $newUser,
                ],
                statusCode: 201,
                context: ['groups' => 'user:read']
            );
        } catch (\Exception $e) {
            return $this->responseFactory->create(
                message: $this->translator->trans('api.user_conroller.registration.messages.failed'),
                errors: [
                    $this->translator->trans('api.user_conroller.registration.errors.generic.title') =>
                    $this->translator->trans('api.user_conroller.registration.errors.generic.value'),
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
                $this->translator->trans('api.auth.login.invalid_credentials.message'),
                errors: [
                    $this->translator->trans('api.auth.login.invalid_credentials.error.title') =>
                    $this->translator->trans('api.auth.login.invalid_credentials.error.value'),
                ],
                statusCode: 401
            );
        }

        $user = $this->authService->getUser($dto);

        if (! $user) {
            return $this->responseFactory->create(
                $this->translator->trans('api.auth.login.incorrect_credentials.message'),
                errors: [
                    $this->translator->trans('api.auth.login.incorrect_credentials.error.title') =>
                    $this->translator->trans('api.auth.login.incorrect_credentials.error.value'),
                ],
                statusCode: 401
            );
        }

        $accessToken = $this->jwtManager->create($user);
        $refreshToken = $this->refreshTokenService->createToken($user);

        return $this->responseFactory->create(
            message: $this->translator->trans('api.auth.login.access_granted'),
            data: [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken->getToken(),
                'user' => $user,
            ],
            context: ['groups' => 'user:read']
        );
    }

    #[Route('/token/refresh', name: 'token_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tokenString = $data['refresh_token'] ?? null;

        if (! $tokenString) {
            return new JsonResponse(['error' => 'Missing refresh token'], 400);
        }

        $refreshToken = $this->refreshTokenService->findValidToken($tokenString);

        if (! $refreshToken) {
            return new JsonResponse(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = $refreshToken->getUser();
        $newAccessToken = $this->jwtManager->create($user);

        return new JsonResponse([
            'access_token' => $newAccessToken,
        ]);
    }
}
