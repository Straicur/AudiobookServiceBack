<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * UnbanUsersCommand
 */
#[AsCommand(
    name       : 'audiobookservice:users:unban',
    description: 'Command is unbanning users with valid date',
)]
class UnbanUsersCommand extends Command
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->userRepository->bannedUsers();

        $io->success('Users unbanned');

        return Command::SUCCESS;
    }
}
