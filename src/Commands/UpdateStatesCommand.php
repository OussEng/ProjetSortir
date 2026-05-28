<?php

namespace App\Commands;

use App\Managers\StateManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update-states')]
class UpdateStatesCommand extends Command
{
    public function __construct(private readonly StateManager $stateManager)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            $this->stateManager->handleAll();
            $output->writeln('States updated: ' . date('H:i:s'));
            sleep(30);
        }
    }
}
