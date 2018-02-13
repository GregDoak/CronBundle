<?php

namespace GregDoak\CronBundle\Command;

use GregDoak\CronBundle\Entity\CronJob;
use GregDoak\CronBundle\Service\CronJobService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class KillCronJobCommand extends Command
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /** @var CronJob[] $cronJobs */
    private $cronJobs = [];
    /** @var array $choiceSelection */
    private $choiceSelection = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->cronJobs = $this->entityManager->getRepository('GregDoakCronBundle:CronJob')->getRunningCronJobs();
        
        foreach ($this->cronJobs as $cronJob) {
            $this->choiceSelection[] = sprintf(
                'PID: %s - Start Date: %s',
                $cronJob->getPid(),
                $cronJob->getStartDate()->format('Y-m-d H:i:s')
            );
        }
    }

    protected function configure(): void
    {
        $this
            ->setName('cron:kill')
            ->setDescription('Kill a running cron job.')
            ->setDefinition(
                [
                    new InputArgument('cron_job_id', InputArgument::REQUIRED, 'The command to kill'),
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
        $cronJob = $input->getArgument('cron_job_id');
        if ($cronJob instanceof CronJob) {
            $cronJobService = new CronJobService($this->entityManager, $cronJob);
            exec(sprintf('kill -9 %d', $cronJob->getPid()));
            $cronJobService->closeAndRelease();
            $output->writeln(sprintf('Trying to kill <comment> PID %d</comment>', $cronJob->getPid()));
        }

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questions = [
            'cron_job_id' => $this->getCronJobIdQuestion(),
        ];

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    /**
     * @return Question
     */
    private function getCronJobIdQuestion()
    {
        $question = new ChoiceQuestion('Please enter the command to be killed:', $this->choiceSelection);
        $question->setValidator(
            function ($value) {
                if ( ! array_key_exists($value, $this->cronJobs)) {
                    throw new \Exception('Please select from the list');
                }

                return $this->cronJobs[$value];
            }
        );

        return $question;
    }
}