<?php

namespace App\Controller;

use App\Entity\User;
use App\Dto\UserRegistrationDto;
use App\Service\ApiResponseFactory;
use App\Service\UserServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users', name: '_api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private UserServiceInterface $userService,
        private ValidatorInterface $validator,
        private ObjectMapperInterface $mapper,
        private ApiResponseFactory $responseFactory,
        private TranslatorInterface $translator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($page < 1 || $limit < 1) {
            return $this->responseFactory->create(
                message: $this->translator->trans('api.user_conroller.get_user_list.validation.message'),
                errors: [
                    $this->translator->trans('api.user_conroller.get_user_list.validation.errors.pagination')
                    => $this->translator->trans('api.user_conroller.get_user_list.validation.errors.value'),
                ],
                statusCode: 400
            );
        }

        $paginator = $this->userService->getUserList($page, $limit);
        $totalItems = \count($paginator);

        return $this->responseFactory->create(
            message: $this->translator->trans('api.user_conroller.get_user_list.result.message'),
            data: [
                'pagination' => $paginator,
                'meta' => [
                    'total' => $totalItems,
                    'page' => $page,
                    'pages' => ceil($totalItems / $limit)
                ],
            ],
            context: ['groups' => 'user:read']
        );
    }

    #[Route('/{id}', name: 'find_by_id', methods: ['GET'])]
    public function findUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->responseFactory->create($this->translator->trans('api.user_conroller.find_by_id.not_found'), statusCode: 404);
        }
        return $this->responseFactory->create(
            $this->translator->trans('api.user_conroller.find_by_id.found'),
            ['user' => $user],
            context: ['groups' => 'user:read']
        );
    }

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
            return $this->responseFactory->create(
                message: $this->translator->trans('api.user_conroller.registration.messages.created'),
                data: ['user' => $newUser],
                statusCode: 201,
                context: ['groups' => 'user:read']
            );
        } catch (\Exception $e) {
            return $this->responseFactory->create(
                message: $this->translator->trans('api.user_conroller.registration.messages.failed'),
                errors: [
                    $this->translator->trans('api.user_conroller.registration.errors.generic.title')
                    => $this->translator->trans('api.user_conroller.registration.errors.generic.value')
                ],
                statusCode: 409
            );
        }
    }
}
