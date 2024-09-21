<?php

declare(strict_types=1);

namespace App\Command;

use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\UserStockCacheTags;
use App\Repository\AudiobookRatingRepository;
use App\Repository\AudiobookRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Fired once a day
 */
#[AsCommand(
    name       : 'audiobookservice:calculate:rating',
    description: 'Calculate audiobooks rating',
)]
class CalculateAudiobooksRatingCommand extends Command
{
    public function __construct(
        private readonly AudiobookRepository $audiobookRepository,
        private readonly AudiobookRatingRepository $ratingRepository,
        private readonly TagAwareCacheInterface $stockCache,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $activeAudiobooks = $this->audiobookRepository->findBy([
            'active' => true,
        ]);

        foreach ($activeAudiobooks as $audiobook) {
            $goodRatings = count($this->ratingRepository->findBy([
                'audiobook' => $audiobook->getId(),
                'rating'    => true,
            ]));

            $audiobookRatings = count($audiobook->getAudiobookRatings());

            if ($audiobookRatings !== 0) {
                $audiobook->setAvgRating(($goodRatings / $audiobookRatings) * 100);

                $this->audiobookRepository->add($audiobook);
            }
        }

        $this->stockCache->invalidateTags([
            AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
            UserStockCacheTags::USER_AUDIOBOOK_RATING->value,
            UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value,
        ]);

        $io->success('Rating calculated successfully.');

        return Command::SUCCESS;
    }
}
