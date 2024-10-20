<?php

declare(strict_types=1);

namespace App\Command;

use App\Builder\NotificationBuilder;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\ProposedAudiobookCategoriesRanges;
use App\Enums\ProposedAudiobooksRanges;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRepository;
use App\Repository\MyListRepository;
use App\Repository\NotificationRepository;
use App\Repository\ProposedAudiobooksRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tool\UserParentalControlTool;
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
    name       : 'audiobookservice:proposed:audiobooks',
    description: 'Command is generating new audiobooks proposed lists for users',
)]
class UserProposedAudiobooksCommand extends Command
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly UserRepository $userRepository,
        private readonly MyListRepository $myListRepository,
        private readonly ProposedAudiobooksRepository $proposedAudiobooksRepository,
        private readonly AudiobookInfoRepository $audiobookInfoRepository,
        private readonly AudiobookCategoryRepository $audiobookCategoryRepository,
        private readonly AudiobookRepository $audiobookRepository,
        private readonly NotificationRepository $notificationRepository,
        private readonly TagAwareCacheInterface $stockCache,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userRole = $this->roleRepository->findOneBy([
            'name' => 'User',
        ]);

        $users = $this->userRepository->getUsersByRole($userRole);

        foreach ($users as $user) {
            $age = null;

            /**
             * Checking if user has parental control
             */
            if ($user->getUserInformation()->getBirthday() !== null) {
                $userParentalControlTool = new UserParentalControlTool();
                $age = $userParentalControlTool->getUserAudiobookAgeValue($user);
            }

            $myList = $user->getMyList();
            $audiobookInfos = $this->audiobookInfoRepository->getActiveAudiobookInfos($user);

            /**
             * Checking if user has sufficient amount of data
             */
            if ((count($myList->getAudiobooks()) + count($audiobookInfos)) <= 10) {
                continue;
            }

            $userWantedCategories = [];

            /**
             * Creating array of points in value and key of categoryId
             */
            foreach ($myList->getAudiobooks() as $audiobook) {
                foreach ($audiobook->getCategories() as $category) {
                    if ($category->getActive()) {
                        if (array_key_exists($category->getId()->__toString(), $userWantedCategories)) {
                            $userWantedCategories[$category->getId()->__toString()] = $userWantedCategories[$category->getId()->__toString()] + 2;
                        } else {
                            $userWantedCategories[$category->getId()->__toString()] = 2;
                        }
                    }
                }
            }

            foreach ($audiobookInfos as $audiobookInfo) {
                foreach ($audiobookInfo->getAudiobook()->getCategories() as $category) {
                    if ($category->getActive()) {
                        if (array_key_exists($category->getId()->__toString(), $userWantedCategories)) {
                            $userWantedCategories[$category->getId()->__toString()] = $userWantedCategories[$category->getId()->__toString()] + 1;
                        } else {
                            $userWantedCategories[$category->getId()->__toString()] = 1;
                        }
                    }
                }
            }

            arsort($userWantedCategories);

            $selectedCategories = array_slice(array_keys($userWantedCategories), 0, 4, true);
            $lastCategory = array_slice(array_keys($userWantedCategories), count($selectedCategories), count($userWantedCategories), true);

            $lastRandomKey = array_rand($lastCategory);

            $selectedCategories[] = $lastCategory[$lastRandomKey];
            /**
             * $selectedCategories now has 5 categories where user is listening most audiobooks
             */

            $proposedAudiobooks = $user->getProposedAudiobooks();

            foreach ($proposedAudiobooks->getAudiobooks() as $audiobook) {
                $proposedAudiobooks->removeAudiobook($audiobook);
            }

            /**
             * Now we are selecting audiobook depend on index of category
             */
            foreach ($selectedCategories as $categoryIndex => $category) {
                $databaseCategory = $this->audiobookCategoryRepository->findOneBy([
                    'id'     => $category,
                    'active' => true,
                ]);

                if ($databaseCategory === null) {
                    continue;
                }

                $audiobooks = $this->audiobookRepository->getActiveCategoryAudiobooks($databaseCategory, $age);

                shuffle($audiobooks);

                $audiobooksAdded = 0;

                foreach ($audiobooks as $audiobook) {
                    if (($categoryIndex === ProposedAudiobookCategoriesRanges::MOST_WANTED->value) && $audiobooksAdded >= ProposedAudiobooksRanges::MOST_WANTED_LIMIT->value) {
                        continue;
                    }
                    if (($categoryIndex === ProposedAudiobookCategoriesRanges::WANTED->value) && $audiobooksAdded >= ProposedAudiobooksRanges::WANTED_LIMIT->value) {
                        continue;
                    }
                    if (($categoryIndex === ProposedAudiobookCategoriesRanges::LESS_WANTED->value) && $audiobooksAdded >= ProposedAudiobooksRanges::LESS_WANTED_LIMIT->value) {
                        continue;
                    }
                    if (($categoryIndex === ProposedAudiobookCategoriesRanges::PROPOSED->value) && $audiobooksAdded >= ProposedAudiobooksRanges::PROPOSED_LIMIT->value) {
                        continue;
                    }
                    if (($categoryIndex === ProposedAudiobookCategoriesRanges::RANDOM->value) && $audiobooksAdded >= ProposedAudiobooksRanges::RANDOM_LIMIT->value) {
                        continue;
                    }

                    /**
                     * checking if audiobook is not in myList
                     */
                    if (!$this->myListRepository->getAudiobookInMyList($user, $audiobook)) {
                        ++$audiobooksAdded;
                        $proposedAudiobooks->addAudiobook($audiobook);
                    }
                }
            }

            $this->proposedAudiobooksRepository->add($proposedAudiobooks);

            /**
             * Sending an notification to user about his new proposed audiobooks
             */
            $notificationBuilder = new NotificationBuilder();

            $notification = $notificationBuilder
                ->setType(NotificationType::PROPOSED)
                ->setAction($proposedAudiobooks->getId())
                ->addUser($user)
                ->setUserAction(NotificationUserType::ADMIN)
                ->setActive(true)
                ->build($this->stockCache);

            $this->notificationRepository->add($notification);
        }

        $this->stockCache->invalidateTags([UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value]);

        $io->success('Proposed audiobooks added for users');

        return Command::SUCCESS;
    }
}
