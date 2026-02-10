<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;

class UserProviderService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ?Security $security = null,
        private readonly ?TokenStorageInterface $tokenStorage = null,
        private readonly ?ContainerInterface $container = null,
        private readonly ?LoggerInterface $logger = null
    ) {}

    /**
     * Returns the current authenticated user or throws when unauthenticated.
     *
     * Preference order:
     * 1. Security helper (recommended)
     * 2. TokenStorageInterface
     * 3. container->get('security.token_storage') (runtime fallback)
     *
     * We throw UserNotFoundException when no authenticated user can be resolved
     * to ensure unauthenticated requests receive a 401 instead of leaking
     * another user's data.
     */
    public function getCurrentUser(): ?User
    {
        // 1) Security helper
        if ($this->security) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                return $user;
            }

            $this->logDebug('security_helper_present_but_no_user');
            throw new UserNotFoundException('No authenticated user found (Security helper present).');
        }

        // 2) TokenStorage (autowired)
        $tokenStorage = $this->tokenStorage;
        if ($tokenStorage) {
            $token = $tokenStorage->getToken();
            if ($token) {
                $user = $token->getUser();
                if ($user instanceof User) {
                    return $user;
                }
                $this->logDebug('token_present_but_user_not_instance', [
                    'token_user_type' => is_object($user) ? get_class($user) : gettype($user),
                ]);
            } else {
                $this->logDebug('token_storage_has_no_token');
            }

            throw new UserNotFoundException('No authenticated user found (token storage present).');
        }

        // 3) Runtime container fallback
        if ($this->container && $this->container->has('security.token_storage')) {
            $svc = $this->container->get('security.token_storage');
            if ($svc instanceof TokenStorageInterface) {
                $token = $svc->getToken();
                if ($token) {
                    $user = $token->getUser();
                    if ($user instanceof User) {
                        return $user;
                    }
                    $this->logDebug('container_token_present_but_user_not_instance', [
                        'token_user_type' => is_object($user) ? get_class($user) : gettype($user),
                    ]);
                } else {
                    $this->logDebug('container_token_storage_has_no_token');
                }
            } else {
                $this->logDebug('container_has_token_storage_but_wrong_type', ['type' => is_object($svc) ? get_class($svc) : gettype($svc)]);
            }

            throw new UserNotFoundException('No authenticated user found (container token storage present).');
        }

        // Nothing available -> fail
        $this->logDebug('no_security_services_available');
        throw new UserNotFoundException('No authenticated user found (no security service available).');
    }

    private function logDebug(string $msg, array $context = []): void
    {
        if ($this->logger) {
            $context = array_merge($context, [
                'security_present' => $this->security !== null,
                'token_storage_injected' => $this->tokenStorage !== null,
                'container_has_token_storage' => (bool) ($this->container && $this->container->has('security.token_storage')),
            ]);
            $this->logger->debug('[UserProviderService] ' . $msg, $context);
        }
    }
}
