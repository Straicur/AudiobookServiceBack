<?php

namespace App\Command;

use App\Entity\Audiobook;
use App\Entity\Role;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\MyListRepository;
use App\Repository\ProposedAudiobooksRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Phpml\Classification\NaiveBayes;

/**
 * UserProposedAudiobooksCommand
 *
 */
#[AsCommand(
    name: 'audiobookservice:proposed:audiobooks',
    description: 'Command is generating new audiobooks proposed list for users',
)]
class UserProposedAudiobooksCommand extends Command
{
    private RoleRepository $roleRepository;
    private UserRepository $userRepository;
    private MyListRepository $myListRepository;
    private ProposedAudiobooksRepository $proposedAudiobooksRepository;
    private AudiobookInfoRepository $audiobookInfoRepository;
    private AudiobookCategoryRepository $audiobookCategoryRepository;
    public function __construct(RoleRepository $roleRepository, UserRepository $userRepository, MyListRepository $myListRepository, ProposedAudiobooksRepository $proposedAudiobooksRepository, AudiobookInfoRepository $audiobookInfoRepository, AudiobookCategoryRepository $audiobookCategoryRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->myListRepository = $myListRepository;
        $this->proposedAudiobooksRepository = $proposedAudiobooksRepository;
        $this->audiobookInfoRepository = $audiobookInfoRepository;
        $this->audiobookCategoryRepository = $audiobookCategoryRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userRole = $this->roleRepository->findOneBy([
            "name" => "User"
        ]);

        $users = $this->userRepository->getUsersByRole($userRole);

        foreach ($users as $user){
            $myList = $user->getMyList();
            $audiobookInfos = $this->audiobookInfoRepository->getActiveAudiobookInfos($user);

            if(count($myList->getAudiobooks())+count($audiobookInfos) >= 10){
                $audiobookCategories = [];
                foreach ($myList->getAudiobooks() as $audiobook) {
                    foreach ($audiobook->getCategories() as $category){
                        $myInfoCategories[] = $category->getId()->__toString();
                    }
                }
                foreach ($audiobookInfos as $audiobookInfo) {
                    foreach ($audiobookInfo->getAudiobook()->getCategories() as $category){
                        $myInfoCategories[] = $category->getId()->__toString();
                    }
                }
                sort($myInfoCategories);
                $mostCat = array_count_values($myInfoCategories);
                arsort($mostCat);
                $categories = array_slice(array_keys($mostCat), 0, 5, true);
                $lRange = (count($categories) * 2) - 1;
                $VALUE = range(0, $lRange);

                $treningCategories = [];
                foreach ($categories as $category) {
                    array_push($treningCategories, [$category, 1]);
                    array_push($treningCategories, [$category, 0]);
                }
                $otherCategories = [];


            }
        }

//        $io->success("Role ${} add successfully.");

        return Command::SUCCESS;
    }
}
