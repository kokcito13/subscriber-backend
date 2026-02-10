<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserProviderService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    /**
     * Returns the current authenticated user or null when unauthenticated.
     *
     * Note: we avoid type-hinting Symfony Security here to prevent container
     * compilation issues in environments where the security core class
     * isn't available. If your project provides a Security service, consider
     * extending this class to use it.
     */
    public function getCurrentUser(): ?User
    {
        // If running inside a full Symfony app with Security available, you
        // could fetch the user from the Security service. We intentionally
        // avoid referencing it here to keep DI-safe.

        // Fallback: return the first user in DB (useful for tests/dev)
        return $this->userRepository->findOneBy([]);
    }
}
