<?php

namespace App\Command;

use App\Repository\AudiobookRatingRepository;
use App\Repository\AudiobookRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CalculateAudiobooksRatingCommand
 *
 */
#[AsCommand(
    name: 'audiobookservice:calculate:rating',
    description: 'Calculate audiobooks rating',
)]
class CalculateAudiobooksRatingCommand extends Command
{
    private AudiobookRepository $audiobookRepository;
    private AudiobookRatingRepository $ratingRepository;

    public function __construct(AudiobookRepository $audiobookRepository, AudiobookRatingRepository $ratingRepository)
    {
        $this->audiobookRepository = $audiobookRepository;
        $this->ratingRepository = $ratingRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $activeAudiobooks = $this->audiobookRepository->findBy([
            "active" => true
        ]);

        foreach ($activeAudiobooks as $audiobook) {
            $goodRatings = count($this->ratingRepository->findBy([
                "audiobook" => $audiobook->getId(),
                "rating" => true
            ]));

            $audiobookRatings = count($audiobook->getAudiobookRatings());

            if ($audiobookRatings !== 0) {
                $audiobook->setAvgRating(($goodRatings / $audiobookRatings) * 100);

                $this->audiobookRepository->add($audiobook);
            }
        }

        $io->success("Rating calculated successfully.");

        return Command::SUCCESS;
    }
}
