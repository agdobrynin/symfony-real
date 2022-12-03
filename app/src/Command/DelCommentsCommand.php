<?php

namespace App\Command;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DelCommentsCommand extends Command
{
    protected static $defaultName = 'app:delete:comments';
    protected static $defaultDescription = 'Remove soft-deleted comments';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('date-to', InputArgument::REQUIRED, 'End date soft-deleted comments')
            ->addArgument('date-from', InputArgument::OPTIONAL, 'Start date soft-deleted comments')
            ->addOption('no-interaction', '-n', InputOption::VALUE_NONE, 'No interaction command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateTimeMax = new \DateTime($input->getArgument('date-to'));
        $dateTimeMin = null;

        if ($dateFrom = $input->getArgument('date-from')) {
            $dateTimeMin = new \DateTime($dateFrom);
        }

        $message = sprintf('<question>Delete comments marked as soft deleted from "%s" to "%s"?</question> [yes|<options=bold,underscore>no</>]: ',
            ($dateTimeMin ? $dateTimeMin->format(\DateTimeInterface::ATOM) : '0000-00-00'),
            $dateTimeMax->format(\DateTimeInterface::ATOM));

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($message, false);

        if ($helper->ask($input, $output, $question)) {
            $resultCount = $this->em->getRepository(Comment::class)
                ->deleteMarkedSoftDeleted($dateTimeMax, $dateTimeMin);

            $output->writeln(sprintf('<info>Delete %s comments</info>', $resultCount));

            return Command::SUCCESS;
        }

        return Command::INVALID;
    }
}
