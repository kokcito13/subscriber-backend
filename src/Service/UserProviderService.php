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
     * Returns the first user from the database as a placeholder.
     * This will be replaced with proper authentication later.
     */
    public function getCurrentUser(): ?User
    {
        return $this->userRepository->findOneBy([]);
    }
}
