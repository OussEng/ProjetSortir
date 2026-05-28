<?php

namespace App\Commands;

use App\Managers\ArchiveManager;
use App\Managers\StateManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:archive-events')]
class ArchiveCommand extends Command
{
    public function __construct(private readonly ArchiveManager $archiveManager)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            $this->archiveManager->archive();
            $output->writeln('Events archived: ' . date('H:i:s'));
            sleep(86400);
        }
    }
}
