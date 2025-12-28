<?php

declare(strict_types = 1);

namespace App\Command;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name       : 'audiobookservice:roles:add',
    description: 'Add role to system',
)]
class AddRoleCommand extends Command
{
    public function __construct(private readonly RoleRepository $roleRepository)
    {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('roleName', InputArgument::REQUIRED, 'Role name');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $roleName = $input->getArgument('roleName');

        $roleEntity = new Role($roleName);

        $this->roleRepository->add($roleEntity);

        $io->success("Role {$roleName} add successfully.");

        return Command::SUCCESS;
    }
}
