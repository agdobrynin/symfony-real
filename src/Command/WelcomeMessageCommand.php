<?php

namespace App\Command;

use App\Service\WelcomeMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WelcomeMessageCommand extends Command
{
    protected static $defaultName = 'app:welcome-message';
    protected static $defaultDescription = 'Make welcome message from console';
    private $welcomeMessage;

    public function __construct(WelcomeMessage $welcomeMessage)
    {
        $this->welcomeMessage = $welcomeMessage;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Input your name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('name');

        $io->info($this->welcomeMessage->welcomeMessage($arg1));
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
