<?php

declare(strict_types = 1);

namespace App\Command;

use App\Repository\UserRepository;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Fired once a day.
 */
#[AsCommand(
    name       : 'audiobookservice:users:unban',
    description: 'Command is unbanning users with valid date smaller than now',
)]
class UnbanUsersCommand extends Command
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->userRepository->unbanBannedUsers();

        $io->success('Users unbanned');

        return Command::SUCCESS;
    }
}
