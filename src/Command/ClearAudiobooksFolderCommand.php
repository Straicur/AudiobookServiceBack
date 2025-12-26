<?php

declare(strict_types = 1);

namespace App\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name       : 'audiobookservice:clear:audiobooks',
    description: 'Clear audiobooks folder',
)]
class ClearAudiobooksFolderCommand extends Command
{
    public function __construct(
        #[Autowire(env: 'MAIN_DIR')] private readonly string $main_dir,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filesystem = new Filesystem();
        if ($this->main_dir && '/' !== $this->main_dir) {
            $audiobookFiles = array_diff(scandir($this->main_dir), ['.', '..']);

            foreach ($audiobookFiles as $file) {
                $filesystem->remove($this->main_dir . '/' . $file);
            }
        }

        $io->success('Folder cleared');

        return Command::SUCCESS;
    }
}
