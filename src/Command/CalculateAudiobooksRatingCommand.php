<?php

declare(strict_types = 1);

namespace App\Command;

use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\UserStockCacheTags;
use App\Util\ProcedureUtil;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Fired once a day.
 */
#[AsCommand(
    name       : 'audiobookservice:calculate:rating',
    description: 'Calculate audiobooks rating. Sum all ratings in one on audiobook entity',
)]
class CalculateAudiobooksRatingCommand extends Command
{
    public function __construct(
        private readonly TagAwareCacheInterface $stockCache,
        private readonly ProcedureUtil $procedureTool,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->procedureTool->executeStoredProcedure('calculate_audiobooks_rating');

        $this->stockCache->invalidateTags([
            AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
            UserStockCacheTags::USER_AUDIOBOOK_RATING->value,
            UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value,
        ]);

        $io->success('Rating calculated successfully.');

        return Command::SUCCESS;
    }
}
