<?php

namespace App\Command;

use App\Entity\AudiobookCategory;
use App\Repository\AudiobookCategoryRepository;
use App\ValueGenerator\CategoryKeyGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * AddCategoryCommand
 *
 */
#[AsCommand(
    name: 'audiobookservice:category:add',
    description: 'Add user to service',
)]
class AddCategoryCommand extends Command
{
    private AudiobookCategoryRepository $audiobookCategoryRepository;

    public function __construct(
        AudiobookCategoryRepository $audiobookCategoryRepository,
    )
    {
        $this->audiobookCategoryRepository = $audiobookCategoryRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Name of category');
        $this->addArgument('parent', InputArgument::OPTIONAL, 'Category parent name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument("name");
        $parent = $input->getArgument("parent");


        $io->text([
            "Name:  " . $name,
            "Parent:  " . $parent,
        ]);

        $categoryKeyGenerator = new CategoryKeyGenerator();

        $newAudiobookCategory = new AudiobookCategory($name, $categoryKeyGenerator);

        if ($parent != null) {
            $parentCategory = $this->audiobookCategoryRepository->findOneBy([
                "name" => $parent
            ]);

            if ($parentCategory != null) {
                $newAudiobookCategory->setParent($parentCategory);
            }
        }
        $newAudiobookCategory->setActive(true);

        $this->audiobookCategoryRepository->add($newAudiobookCategory);

        $io = new SymfonyStyle($input, $output);

        $io->success('Success');

        return Command::SUCCESS;
    }
}