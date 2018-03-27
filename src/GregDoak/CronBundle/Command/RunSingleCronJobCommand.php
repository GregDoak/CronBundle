<?php

namespace GregDoak\CronBundle\Command;

use GregDoak\CronBundle\Entity\CronJob;
use GregDoak\CronBundle\Entity\CronJobTask;
use GregDoak\CronBundle\Service\CronJobService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class RunSingleCronJobCommand
 * @package GregDoak\CronBundle\Command
 */
class RunSingleCronJobCommand extends Command
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /** @var string $projectDirectory */
    private $projectDirectory;
    /** @var CronJobTask[] $cronJobsTask */
    private $cronJobTasks;
    /** @var array $choiceSelection */
    private $choiceSelection = [];

    /**
     * RunSingleCronJobCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $projectDirectory
     */
    public function __construct(EntityManagerInterface $entityManager, string $projectDirectory)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->cronJobTasks = $this->entityManager->getRepository('GregDoakCronBundle:CronJobTask')->getActiveTasks();
        $this->projectDirectory = $projectDirectory;

        foreach ($this->cronJobTasks as $cronJobTask) {
            $this->choiceSelection[] = $cronJobTask->getCommand();
        }
    }

    protected function configure(): void
    {
        $this
            ->setName('cron:run:single')
            ->setDescription('Run a single cron job.')
            ->setDefinition(
                [
                    new InputArgument('cron_command', InputArgument::REQUIRED, 'The command to run'),
                ]
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $cronJobTask = $input->getArgument('cron_command');
        if($cronJobTask instanceof CronJobTask) {
            $cronJobService = new CronJobService($this->entityManager, $this->projectDirectory);
            $cronJobService
                ->reserveTask($cronJobTask)
                ->execute();
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questions = [
            'cron_command' => $this->getCommandQuestion(),
        ];

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    /**
     * @return ChoiceQuestion
     */
    private function getCommandQuestion(): ChoiceQuestion
    {
        $question = new ChoiceQuestion('Please enter the command to be executed:', $this->choiceSelection);
        $question->setValidator(
            function ($value) {
                if ( ! array_key_exists($value, $this->cronJobTasks)) {
                    throw new \Exception('Please select from the list');
                }

                return $this->cronJobTasks[$value];
            }
        );

        return $question;
    }
}