<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', strtolower($email))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return array of users that match the given API key prefix.
     * The authenticator will iterate candidates and verify the full token hash.
     *
     * @return User[]
     */
    public function findByApiKeyPrefix(string $prefix): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.apiKeyPrefix = :prefix')
            ->andWhere('u.apiKeyHash IS NOT NULL')
            ->setParameter('prefix', $prefix)
            ->getQuery()
            ->getResult();
    }
}
