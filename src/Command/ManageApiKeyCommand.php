<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ManageApiKeyCommand extends Command
{
    protected static $defaultName = 'app:manage-api-key';

    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create an API token for a user')
            ->addArgument('email', InputArgument::REQUIRED, 'User email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user instanceof User) {
            $output->writeln('<error>User not found: ' . $email . '</error>');
            return Command::FAILURE;
        }

        $token = bin2hex(random_bytes(32));
        $prefix = substr($token, 0, 8);
        $hash = defined('PASSWORD_ARGON2ID') ? password_hash($token, PASSWORD_ARGON2ID) : password_hash($token, PASSWORD_BCRYPT);

        $user->setApiKeyPrefix($prefix);
        $user->setApiKeyHash($hash);
        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('<info>TOKEN: ' . $token . '</info>');

        return Command::SUCCESS;
    }
}
