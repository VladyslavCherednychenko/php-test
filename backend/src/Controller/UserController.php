<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users', name: '_api_users_')]
class UserController extends AbstractController
{
    private UserServiceInterface $userService;
    private ValidatorInterface $validator;

    public function __construct(UserServiceInterface $userService, ValidatorInterface $validator)
    {
        $this->userService = $userService;
        $this->validator = $validator;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($page < 1 || $limit < 1) {
            return $this->json(['error' => 'Pagination parameters must be greater than 0.'], 400);
        }

        $paginator = $this->userService->getUserList($page, $limit);
        $totalItems = count($paginator);

        return $this->json([
            'data' => $paginator,
            'meta' => [
                'total' => $totalItems,
                'page' => $page,
                'pages' => ceil($totalItems / $limit)
            ]
        ], 200, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'find_by_id', methods: ['GET'])]
    public function findUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }
        return $this->json(['user' => $user], 200, [], ['groups' => 'user:read']);
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 400);
        }

        try {
            $newUser = $this->userService->createUser($user);

            return $this->json([
                'message' => 'User created',
                'user' => $newUser
            ], 201, [], ['groups' => 'user:read']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Could not create user.'], 409);
        }
    }
}
