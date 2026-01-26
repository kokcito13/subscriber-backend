<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * @return Subscription[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Subscription[]
     */
    public function findActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Subscription::STATUS_ACTIVE)
            ->orderBy('s.nextBillingDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Subscription[]
     */
    public function findUpcomingBilling(User $user, \DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.status = :status')
            ->andWhere('s.nextBillingDate <= :date')
            ->setParameter('user', $user)
            ->setParameter('status', Subscription::STATUS_ACTIVE)
            ->setParameter('date', $date)
            ->orderBy('s.nextBillingDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
