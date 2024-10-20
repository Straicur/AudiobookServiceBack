<?php

declare(strict_types=1);

namespace App\Command;

use App\Enums\Cache\UserStockCacheTags;
use App\Repository\NotificationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Fired every 5 minutes
 */
#[AsCommand(
    name       : 'audiobookservice:notifications:activate',
    description: 'Activate Notifications where date of activation is smaller than now',
)]
class ActivateNotificationsCommand extends Command
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
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

        $notifications = $this->notificationRepository->getNotificationsToActivate();
        $notificationsCount = count($notifications);

        foreach ($notifications as $notification) {
            $notification->setActive(true);
            $this->notificationRepository->add($notification);
        }

        if ($notificationsCount > 0) {
            $this->stockCache->invalidateTags([UserStockCacheTags::USER_NOTIFICATIONS->value]);
        }

        $io->success("Activated $notificationsCount notifications successfully.");

        return Command::SUCCESS;
    }
}
