<?php

declare(strict_types = 1);

namespace App\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name       : 'audiobookservice:clear:audiobooks',
    description: 'Clear audiobooks folder',
)]
class ClearAudiobooksFolderCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filesystem = new Filesystem();
        if ($_ENV['MAIN_DIR'] && '/' !== $_ENV['MAIN_DIR']) {
            $audiobookFiles = array_diff(scandir($_ENV['MAIN_DIR']), ['.', '..']);

            foreach ($audiobookFiles as $file) {
                $filesystem->remove($_ENV['MAIN_DIR'] . '/' . $file);
            }
        }

        $io->success('Folder cleared');

        return Command::SUCCESS;
    }
}
