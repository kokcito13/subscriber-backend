<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-data',
    description: 'Seed database with sample users and subscriptions for development'
)]
class SeedDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Seeding database with sample data');

        // Check if users already exist
        $existingUsers = $this->entityManager->getRepository(User::class)->findAll();
        if (!empty($existingUsers)) {
            $io->warning('Users already exist in the database. Skipping seed.');
            return Command::SUCCESS;
        }

        // Create sample users
        $user1 = new User();
        $user1->setEmail('john.doe@example.com');
        $this->entityManager->persist($user1);

        $user2 = new User();
        $user2->setEmail('jane.smith@example.com');
        $this->entityManager->persist($user2);

        $this->entityManager->flush();
        $io->success('Created 2 sample users');

        // Create sample subscriptions for user1
        $subscription1 = new Subscription();
        $subscription1->setUser($user1);
        $subscription1->setName('Netflix Premium');
        $subscription1->setAmount('15.99');
        $subscription1->setCurrency('USD');
        $subscription1->setBillingPeriod(Subscription::BILLING_PERIOD_MONTHLY);
        $subscription1->setBillingDayOfMonth(15);
        $subscription1->setNextBillingDate(new \DateTimeImmutable('2026-02-15'));
        $subscription1->setCategory(Subscription::CATEGORY_STREAMING);
        $subscription1->setStatus(Subscription::STATUS_ACTIVE);
        $this->entityManager->persist($subscription1);

        $subscription2 = new Subscription();
        $subscription2->setUser($user1);
        $subscription2->setName('Spotify Family');
        $subscription2->setAmount('16.99');
        $subscription2->setCurrency('USD');
        $subscription2->setBillingPeriod(Subscription::BILLING_PERIOD_MONTHLY);
        $subscription2->setBillingDayOfMonth(1);
        $subscription2->setNextBillingDate(new \DateTimeImmutable('2026-02-01'));
        $subscription2->setCategory(Subscription::CATEGORY_STREAMING);
        $subscription2->setStatus(Subscription::STATUS_ACTIVE);
        $this->entityManager->persist($subscription2);

        // Create sample subscription for user2
        $subscription3 = new Subscription();
        $subscription3->setUser($user2);
        $subscription3->setName('ChatGPT Plus');
        $subscription3->setAmount('20.00');
        $subscription3->setCurrency('USD');
        $subscription3->setBillingPeriod(Subscription::BILLING_PERIOD_MONTHLY);
        $subscription3->setBillingDayOfMonth(10);
        $subscription3->setNextBillingDate(new \DateTimeImmutable('2026-02-10'));
        $subscription3->setCategory(Subscription::CATEGORY_AI);
        $subscription3->setStatus(Subscription::STATUS_ACTIVE);
        $this->entityManager->persist($subscription3);

        // Create a yearly subscription
        $subscription4 = new Subscription();
        $subscription4->setUser($user1);
        $subscription4->setName('Adobe Creative Cloud');
        $subscription4->setAmount('599.88');
        $subscription4->setCurrency('USD');
        $subscription4->setBillingPeriod(Subscription::BILLING_PERIOD_YEARLY);
        $subscription4->setBillingDayOfMonth(1);
        $subscription4->setBillingMonthOfYear(1);
        $subscription4->setNextBillingDate(new \DateTimeImmutable('2027-01-01'));
        $subscription4->setCategory(Subscription::CATEGORY_WORK);
        $subscription4->setStatus(Subscription::STATUS_ACTIVE);
        $this->entityManager->persist($subscription4);

        // Create a weekly subscription
        $subscription5 = new Subscription();
        $subscription5->setUser($user2);
        $subscription5->setName('Weekly Magazine');
        $subscription5->setAmount('4.99');
        $subscription5->setCurrency('USD');
        $subscription5->setBillingPeriod(Subscription::BILLING_PERIOD_WEEKLY);
        $subscription5->setNextBillingDate(new \DateTimeImmutable('2026-02-02'));
        $subscription5->setCategory(Subscription::CATEGORY_OTHER);
        $subscription5->setStatus(Subscription::STATUS_ACTIVE);
        $this->entityManager->persist($subscription5);

        $this->entityManager->flush();
        $io->success('Created 5 sample subscriptions');

        $io->success('Database seeded successfully!');
        $io->table(
            ['User Email', 'Subscriptions'],
            [
                ['john.doe@example.com', '3'],
                ['jane.smith@example.com', '2'],
            ]
        );

        return Command::SUCCESS;
    }
}
