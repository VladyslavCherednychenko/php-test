<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\IUserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users', name: '_api_users_')]
class UserController extends AbstractController
{
    private IUserRepository $userRepository;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(IUserRepository $userRepository, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $paginator = $this->userRepository->getUserList($page, $limit);
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
        $user = $this->userRepository->getUserById($id);

        if ($user == null) {
            return $this->json([
                'message' => 'User not found',
                'user' => $user
            ], 404, [], ['groups' => 'user:read']);
        }

        return $this->json([
            'message' => 'User found',
            'user' => $user
        ], 201, [], ['groups' => 'user:read']);
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setEmail($data['email'] ?? '');

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);

        try {
            $newUser = $this->userRepository->createUser($data['email'], $hashedPassword);
            return $this->json([
                'message' => 'User created',
                'user' => $newUser
            ], 201, [], ['groups' => 'user:read']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Email already exists or DB error'], 409);
        }
    }
}
