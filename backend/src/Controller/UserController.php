<?php

namespace App\Controller;

use App\Service\ApiResponseFactory;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private UserServiceInterface $userService,
        private ApiResponseFactory $responseFactory,
        private TranslatorInterface $translator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($page < 1 || $limit < 1) {
            return $this->responseFactory->error(
                message: $this->translator->trans('api.users.list.validation.message'),
                errors: [
                    'pagination' => $this->translator->trans('api.users.list.validation.errors.value'),
                ]
            );
        }

        $paginator = $this->userService->getUserList($page, $limit);
        $totalItems = \count($paginator);

        return $this->responseFactory->success(
            message: $this->translator->trans('api.users.list.result_message'),
            data: [
                'pagination' => $paginator,
                'meta' => [
                    'total' => $totalItems,
                    'page' => $page,
                    'pages' => ceil($totalItems / $limit),
                ],
            ],
            groups: ['user:read']
        );
    }

    #[Route('/{id<\d+>}', name: 'find_by_id', methods: ['GET'])]
    public function findUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
            return $this->responseFactory->error($this->translator->trans('api.users.find_by_id.not_found'), statusCode: 404);
        }
        return $this->responseFactory->success(
            message: $this->translator->trans('api.users.find_by_id.found'),
            data: ['user' => $user],
            groups: ['user:read']
        );
    }
}
