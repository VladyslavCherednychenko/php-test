<?php
namespace App\Controller;

use App\Service\ApiResponseFactory;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/users', name: '_api_users_')]
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
            return $this->responseFactory->create(
                message: $this->translator->trans('api.user_conroller.get_user_list.validation.message'),
                errors: [
                    $this->translator->trans('api.user_conroller.get_user_list.validation.errors.pagination') =>
                    $this->translator->trans('api.user_conroller.get_user_list.validation.errors.value'),
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
                    'pages' => ceil($totalItems / $limit),
                ],
            ],
            context: ['groups' => 'user:read']
        );
    }

    #[Route('/{id}', name: 'find_by_id', methods: ['GET'])]
    public function findUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
            return $this->responseFactory->create($this->translator->trans('api.user_conroller.find_by_id.not_found'), statusCode: 404);
        }
        return $this->responseFactory->create(
            $this->translator->trans('api.user_conroller.find_by_id.found'),
            ['user' => $user],
            context: ['groups' => 'user:read']
        );
    }
}
