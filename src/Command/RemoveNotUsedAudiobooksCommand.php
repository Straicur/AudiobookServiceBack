<?php

declare(strict_types = 1);

namespace App\Command;

use App\Repository\AudiobookRepository;
use App\Service\Admin\Audiobook\AudiobookService;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use const GLOB_NOSORT;

/**
 * Fired once a week.
 */
#[AsCommand(
    name       : 'audiobookservice:audioobooks:remove:notused',
    description: 'Command is removing not used audiobooks from files',
)]
class RemoveNotUsedAudiobooksCommand extends Command
{
    public function __construct(
        private readonly AudiobookRepository $audiobookRepository,
        private readonly AudiobookService $audiobookService,
        #[Autowire(env: 'MAIN_DIR')] private readonly string $main_dir,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (glob(rtrim($this->main_dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $isInRepo = $this->audiobookRepository->findOneBy([
                'fileName' => $each,
            ]);

            if (null === $isInRepo) {
                /*
                 * Delete if there is no audiobook file path in database
                 */
                $this->audiobookService->removeFolder($each);
            }
        }

        $io->success('Audiobooks files deleted');

        return Command::SUCCESS;
    }
}
