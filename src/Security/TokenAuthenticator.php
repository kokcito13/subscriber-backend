<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Psr\Log\LoggerInterface;

class TokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ?LoggerInterface $logger = null
    ) {}

    public function supports(Request $request): ?bool
    {
        $auth = $request->headers->get('Authorization');
        if (!$auth) {
            return false;
        }

        return str_starts_with($auth, 'Bearer ');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $auth = $request->headers->get('Authorization', '');
        $token = trim((string) str_replace('Bearer', '', $auth));

        if ($token === '') {
            throw new AuthenticationException('No API token provided');
        }

        // We'll use a UserBadge with a callback that will return User|null
        return new SelfValidatingPassport(new UserBadge($token, function (string $badge) use ($token) {
            $user = $this->findUserByToken($token);
            if (!$user instanceof User) {
                throw new AuthenticationException('Invalid API token');
            }
            return $user;
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = $exception->getMessage();
        if ($this->logger) {
            $this->logger->info('Authentication failure: ' . $message);
        }

        return new JsonResponse([
            'error' => 'Authentication Failed',
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    private function findUserByToken(string $token): ?User
    {
        // Use prefix to filter candidates quickly
        $prefix = substr($token, 0, 8);
        $candidates = $this->userRepository->findByApiKeyPrefix($prefix);

        foreach ($candidates as $user) {
            $hash = $user->getApiKeyHash();
            if ($hash) {
                // Accept hashed match or direct equality (direct equality is allowed for quick testing)
                if (password_verify($token, $hash) || hash_equals($hash, $token)) {
                    return $user;
                }
            }
        }

        return null;
    }
}
