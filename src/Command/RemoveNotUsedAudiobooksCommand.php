<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\AudiobookRepository;
use App\Service\Admin\Audiobook\AudiobookService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name       : 'audiobookservice:audioobooks:remove:notused',
    description: 'Command is removing not used audiobooks from files',
)]
class RemoveNotUsedAudiobooksCommand extends Command
{
    public function __construct(
        private readonly AudiobookRepository $audiobookRepository,
        private readonly AudiobookService $audiobookService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (glob(rtrim($_ENV['MAIN_DIR'], '/') . '/*', GLOB_NOSORT) as $each) {
            $isInRepo = $this->audiobookRepository->findOneBy([
                'fileName' => $each,
            ]);

            if ($isInRepo === null) {
                $this->audiobookService->removeFolder($each);
            }
        }

        $io->success('Audiobooks files deleted');

        return Command::SUCCESS;
    }
}
