<?php

namespace App\Command;

use App\Builder\NotificationBuilder;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\ProposedAudiobookCategoriesRanges;
use App\Enums\ProposedAudiobooksRanges;
use App\Exception\NotificationException;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRepository;
use App\Repository\MyListRepository;
use App\Repository\NotificationRepository;
use App\Repository\ProposedAudiobooksRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * UserProposedAudiobooksCommand
 */
#[AsCommand(
    name: 'audiobookservice:proposed:audiobooks',
    description: 'Command is generating new audiobooks proposed lists for users',
)]
class UserProposedAudiobooksCommand extends Command
{
    private RoleRepository $roleRepository;
    private UserRepository $userRepository;
    private MyListRepository $myListRepository;
    private ProposedAudiobooksRepository $proposedAudiobooksRepository;
    private AudiobookInfoRepository $audiobookInfoRepository;
    private AudiobookCategoryRepository $audiobookCategoryRepository;
    private AudiobookRepository $audiobookRepository;
    private NotificationRepository $notificationRepository;


    public function __construct(RoleRepository $roleRepository, UserRepository $userRepository, MyListRepository $myListRepository, ProposedAudiobooksRepository $proposedAudiobooksRepository, AudiobookInfoRepository $audiobookInfoRepository, AudiobookCategoryRepository $audiobookCategoryRepository, AudiobookRepository $audiobookRepository, NotificationRepository $notificationRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->myListRepository = $myListRepository;
        $this->proposedAudiobooksRepository = $proposedAudiobooksRepository;
        $this->audiobookInfoRepository = $audiobookInfoRepository;
        $this->audiobookCategoryRepository = $audiobookCategoryRepository;
        $this->audiobookRepository = $audiobookRepository;
        $this->notificationRepository = $notificationRepository;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws NotificationException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userRole = $this->roleRepository->findOneBy([
            "name" => "User"
        ]);

        $users = $this->userRepository->getUsersByRole($userRole);

        foreach ($users as $user) {

            $myList = $user->getMyList();
            $audiobookInfos = $this->audiobookInfoRepository->getActiveAudiobookInfos($user);

            if (count($myList->getAudiobooks()) + count($audiobookInfos) >= 10) {

                $userWantedCategories = [];

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

                $proposedAudiobooks = $user->getProposedAudiobooks();

                foreach ($proposedAudiobooks->getAudiobooks() as $audiobook) {
                    $proposedAudiobooks->removeAudiobook($audiobook);
                }

                foreach ($selectedCategories as $categoryIndex => $category) {

                    $databaseCategory = $this->audiobookCategoryRepository->findOneBy([
                        "id" => $category,
                        "active" => true
                    ]);

                    if ($databaseCategory != null) {

                        $audiobooks = $this->audiobookRepository->getActiveCategoryAudiobooks($databaseCategory);

                        shuffle($audiobooks);

                        $audiobooksAdded = 0;

                        foreach ($audiobooks as $audiobook) {
                            if ($categoryIndex == ProposedAudiobookCategoriesRanges::MOST_WANTED->value) {
                                if ($audiobooksAdded >= ProposedAudiobooksRanges::MOST_WANTED_LIMIT->value) {
                                    continue;
                                }
                            }
                            if ($categoryIndex == ProposedAudiobookCategoriesRanges::WANTED->value) {
                                if ($audiobooksAdded >= ProposedAudiobooksRanges::WANTED_LIMIT->value) {
                                    continue;
                                }
                            }
                            if ($categoryIndex == ProposedAudiobookCategoriesRanges::LESS_WANTED->value) {
                                if ($audiobooksAdded >= ProposedAudiobooksRanges::LESS_WANTED_LIMIT->value) {
                                    continue;
                                }
                            }
                            if ($categoryIndex == ProposedAudiobookCategoriesRanges::PROPOSED->value) {
                                if ($audiobooksAdded >= ProposedAudiobooksRanges::PROPOSED_LIMIT->value) {
                                    continue;
                                }
                            }
                            if ($categoryIndex == ProposedAudiobookCategoriesRanges::RANDOM->value) {
                                if ($audiobooksAdded >= ProposedAudiobooksRanges::RANDOM_LIMIT->value) {
                                    continue;
                                }
                            }

                            if (!$this->myListRepository->getAudiobookINMyList($user, $audiobook)) {
                                $audiobooksAdded = $audiobooksAdded + 1;
                                $proposedAudiobooks->addAudiobook($audiobook);
                            }
                        }
                    }
                }
                $this->proposedAudiobooksRepository->add($proposedAudiobooks);

                $notificationBuilder = new NotificationBuilder();

                $notification = $notificationBuilder
                    ->setType(NotificationType::USER_DELETE_DECLINE)
                    ->setAction($proposedAudiobooks->getId())
                    ->setUser($user)
                    ->setUserAction(NotificationUserType::ADMIN)
                    ->build();

                $this->notificationRepository->add($notification);
            }
        }

        $io->success("Proposed audiobooks added for users");

        return Command::SUCCESS;
    }
}
