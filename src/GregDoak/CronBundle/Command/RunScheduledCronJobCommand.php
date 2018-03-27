<?php

namespace GregDoak\CronBundle\Command;

use GregDoak\CronBundle\Service\CronJobService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunScheduledCronJobCommand
 * @package GregDoak\CronBundle\Command
 */
class RunScheduledCronJobCommand extends Command
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /** @var string $projectDirectory */
    private $projectDirectory;

    /**
     * RunScheduledCronJobCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $projectDirectory
     */
    public function __construct(EntityManagerInterface $entityManager, string $projectDirectory)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->projectDirectory = $projectDirectory;
    }

    protected function configure(): void
    {
        $this
            ->setName('cron:run:scheduled')
            ->setDescription('Run the scheduled cron jobs.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $cronJobService = new CronJobService($this->entityManager, $this->projectDirectory);
        $cronJobService
            ->reserveScheduledTasks()
            ->execute();
    }
}