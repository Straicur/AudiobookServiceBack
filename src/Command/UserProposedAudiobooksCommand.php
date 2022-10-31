<?php

namespace App\Command;

use App\Entity\Audiobook;
use App\Enums\ProposedAudiobooksRanges;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRepository;
use App\Repository\MyListRepository;
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
    private AudiobookRepository $audiobookRepository;

    public function __construct(RoleRepository $roleRepository, UserRepository $userRepository, MyListRepository $myListRepository, ProposedAudiobooksRepository $proposedAudiobooksRepository, AudiobookInfoRepository $audiobookInfoRepository, AudiobookCategoryRepository $audiobookCategoryRepository, AudiobookRepository $audiobookRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->myListRepository = $myListRepository;
        $this->proposedAudiobooksRepository = $proposedAudiobooksRepository;
        $this->audiobookInfoRepository = $audiobookInfoRepository;
        $this->audiobookCategoryRepository = $audiobookCategoryRepository;
        $this->audiobookRepository = $audiobookRepository;

        parent::__construct();
    }

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
                $audiobookCategories = [];
                $myInfoCategories = [];
                foreach ($myList->getAudiobooks() as $audiobook) {
                    foreach ($audiobook->getCategories() as $category) {
                        if($category->getActive()){
                            if(array_key_exists($category->getId()->__toString(),$myInfoCategories)){
                                $myInfoCategories[$category->getId()->__toString()] = $myInfoCategories[$category->getId()->__toString()]+2;
                            }
                            else{
                                $myInfoCategories[$category->getId()->__toString()] = 2;
                            }
                        }
                    }
                }
                foreach ($audiobookInfos as $audiobookInfo) {
                    foreach ($audiobookInfo->getAudiobook()->getCategories() as $category) {
                        if($category->getActive()) {
                            if (array_key_exists($category->getId()->__toString(), $myInfoCategories)) {
                                $myInfoCategories[$category->getId()->__toString()] = $myInfoCategories[$category->getId()->__toString()] + 1;
                            } else {
                                $myInfoCategories[$category->getId()->__toString()] = 1;
                            }
                        }
                    }
                }

                arsort($myInfoCategories);

                $categories = array_slice(array_keys($myInfoCategories), 0, 4, true);
                $lastCategory = array_slice(array_keys($myInfoCategories), count($categories), count($myInfoCategories), true);

                $lastRandomKey = array_rand($lastCategory);

                $categories[] = $lastCategory[$lastRandomKey];

                $proposedAudiobooks = $user->getProposedAudiobooks();

                foreach ($proposedAudiobooks->getAudiobooks() as $audiobook){
                    $proposedAudiobooks->removeAudiobook($audiobook);
                }

                foreach ($categories as $categoryIndex => $category){

                    $databaseCategory = $this->audiobookCategoryRepository->findOneBy([
                        "id"=>$category,
                        "active"=>true
                    ]);

                    if($databaseCategory != null){
                        $audiobooks = $this->audiobookRepository->getRandomSortedCategoryAudiobooks($databaseCategory);
                        foreach ($audiobooks as $audiobookIndex => $audiobook){
                            //todo zostaje do rozkminy jak dobrać te limity z enuma albo coś innego wymyślić
                            // i do tego jeszcze te randomowe audiobooki
                            if(!$this->myListRepository->getAudiobookINMyList($user,$audiobook)){
                                $proposedAudiobooks->addAudiobook($audiobook);
                            }
                        }
                    }
                }
                $this->proposedAudiobooksRepository->add($proposedAudiobooks);
            }
        }

//        $io->success("Role ${} add successfully.");

        return Command::SUCCESS;
    }
}
