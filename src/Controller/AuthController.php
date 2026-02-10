<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('/api/auth', name: 'api_auth', methods: ['POST'])]
    public function auth(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '', true);
        if (!is_array($data) || empty($data['email'])) {
            return new JsonResponse(['error' => 'Missing email field'], Response::HTTP_BAD_REQUEST);
        }

        $email = strtolower(trim((string) $data['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Invalid email'], Response::HTTP_BAD_REQUEST);
        }

        // Find existing user
        $user = $this->userRepository->findByEmail($email);

        if (!$user instanceof User) {
            // Create new user
            $user = new User();
            $user->setEmail($email);
            try {
                $this->em->persist($user);
                $this->em->flush();
            } catch (UniqueConstraintViolationException $e) {
                // Race: another request created the user, reload
                $this->em->clear();
                $user = $this->userRepository->findByEmail($email);
                if (!$user instanceof User) {
                    return new JsonResponse(['error' => 'Could not create user'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        // If user already has a stored API token-like value, return it.
        // We treat values that start with "$" as password_hash outputs (non-recoverable),
        // in that case we generate a new token and store it in plaintext so subsequent calls
        // will return the same token. For simplicity (requested), tokens are stored as plain
        // values in `apiKeyHash` so they can be returned later. The authenticator accepts
        // either password_verify or direct equality, so plaintext tokens will still authenticate.
        $existing = $user->getApiKeyHash();
        if ($existing !== null) {
            // If it's not a password hash (doesn't start with '$'), assume it's the token and return it
            if (!str_starts_with($existing, '$')) {
                // Ensure prefix exists
                if (!$user->getApiKeyPrefix()) {
                    $user->setApiKeyPrefix(substr((string) $existing, 0, 8));
                    $this->em->persist($user);
                    $this->em->flush();
                }

                return new JsonResponse([
                    'token' => $existing,
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                    ],
                ]);
            }

            // Otherwise it looks hashed/unrecoverable; we will generate a new token, overwrite stored value,
            // and return it (so next call will return same token).
        }

        // Generate a new API token (hex, 64 chars)
        try {
            $token = bin2hex(random_bytes(32));
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Could not generate token'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $prefix = substr($token, 0, 8);
        // Store token as plain so it can be returned on subsequent calls (authenticator accepts direct equality)
        $user->setApiKeyPrefix($prefix);
        $user->setApiKeyHash($token);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ],
        ]);
    }
}
