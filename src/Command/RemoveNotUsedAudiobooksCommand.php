<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\AudiobookRepository;
use App\Service\AudiobookService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * RemoveNotUsedAudiobooksCommand
 */
#[AsCommand(
    name       : 'audiobookservice:audioobooks:remove:notused',
    description: 'Command is removing not used audiobooks from files',
)]
class RemoveNotUsedAudiobooksCommand extends Command
{
    private AudiobookRepository $audiobookRepository;
    private AudiobookService $audiobookService;

    public function __construct(AudiobookRepository $audiobookRepository, AudiobookService $audiobookService)
    {
        $this->audiobookRepository = $audiobookRepository;
        $this->audiobookService = $audiobookService;
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
