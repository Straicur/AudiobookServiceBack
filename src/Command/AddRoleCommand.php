<?php

namespace App\Command;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * AddRoleCommand
 *
 */
#[AsCommand(
    name: 'audiobookservice:roles:add',
    description: 'Add role to system',
)]
class AddRoleCommand extends Command
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument("roleName", InputArgument::REQUIRED, "Role name");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $roleName = $input->getArgument('roleName');

        $roleEntity = new Role($roleName);

        $this->roleRepository->add($roleEntity);

        $io->success("Role ${roleName} add successfully.");

        return Command::SUCCESS;
    }
}
