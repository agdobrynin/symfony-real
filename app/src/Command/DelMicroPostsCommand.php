<?php

namespace App\Command;

use App\Entity\MicroPost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DelMicroPostsCommand extends Command
{
    protected static $defaultName = 'app:delete:posts';
    protected static $defaultDescription = 'Remove soft-deleted micro posts';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('date-to', InputArgument::REQUIRED, 'End date soft-deleted micro posts')
            ->addArgument('date-from', InputArgument::OPTIONAL, 'Start date soft-deleted micro posts')
            ->addOption('no-interaction', '-n', InputOption::VALUE_NONE, 'No interaction command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateTimeMax = new \DateTime($input->getArgument('date-to'));
        $dateTimeMin = null;

        if ($dateFrom = $input->getArgument('date-from')) {
            $dateTimeMin = new \DateTime($dateFrom);
        }

        if ($dateTimeMin && $dateTimeMin > $dateTimeMax) {
            $message = sprintf('Date from %s must be less than date to %s',
                $dateTimeMin->format(\DateTimeInterface::ATOM), $dateTimeMax->format(\DateTimeInterface::ATOM));

            throw new \LogicException($message);
        }

        $message = sprintf('<question>Delete micro posts with comments marked as soft deleted from "%s" to "%s"?</question> [yes|no]: ',
            ($dateTimeMin ? $dateTimeMin->format(\DateTimeInterface::ATOM) : '0000-00-00'),
            $dateTimeMax->format(\DateTimeInterface::ATOM));

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($message, true);

        if ($helper->ask($input, $output, $question)) {
            $resultCount = $this->em->getRepository(MicroPost::class)
                ->deleteMarkedSoftDeleted($dateTimeMax, $dateTimeMin);

            $output->writeln(sprintf('<info>Delete %s micro posts</info>', $resultCount));

            return Command::SUCCESS;
        }

        return Command::INVALID;
    }
}
