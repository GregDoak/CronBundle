<?php

namespace GregDoak\CronBundle\Command;

use GregDoak\CronBundle\Entity\CronJobTask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateCronJobTaskCommand extends Command
{
    private $intervalContexts = ['year', 'month', 'day', 'hour', 'minute', 'second'];
    private $priorities = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /** @var ValidatorInterface $validator */
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    protected function configure(): void
    {
        $this
            ->setName('cron:create')
            ->setDescription('Create a cron job.')
            ->setDefinition(
                [
                    new InputArgument('cron_command', InputArgument::REQUIRED, 'The command to run'),
                    new InputArgument('start', InputArgument::REQUIRED, 'The start date'),
                    new InputArgument('interval', InputArgument::REQUIRED, 'The interval between runs'),
                    new InputArgument('context', InputArgument::REQUIRED, 'The context of the interval'),
                    new InputArgument('priority', InputArgument::REQUIRED, 'The run priority of the job'),
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
        $cronJobTask = new CronJobTask();
        $cronJobTask
            ->setCommand($input->getArgument('cron_command'))
            ->setStartDate($input->getArgument('start'))
            ->setIntervalPeriod($input->getArgument('interval'))
            ->setIntervalContext($input->getArgument('context'))
            ->setPriority($input->getArgument('priority'))
            ->setNextRun();

        $errors = $this->validator->validate($cronJobTask);
        foreach ($errors as $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));
        }

        if (\count($errors) === 0) {
            try {

                $this->entityManager->persist($cronJobTask);
                $this->entityManager->flush();
                $output->writeln(sprintf('Saved Cron Job <comment>%s</comment>', $cronJobTask->getCommand()));

            } catch (\Exception $exception) {
                $output->writeln('<error>Failed to save cron job to the database</error>');
            }
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
            'start' => $this->getStartDateQuestion(),
            'interval' => $this->getIntervalQuestion(),
            'context' => $this->getIntervalContextQuestion(),
            'priority' => $this->getPriorityQuestion(),
        ];

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    /**
     * @return Question
     */
    private function getCommandQuestion()
    {
        $question = new Question('Please enter the command to be executed:');
        $question->setValidator(
            function ($value) {
                if (empty($value)) {
                    throw new \Exception('Command can not be empty');
                }

                return $value;
            }
        );

        return $question;
    }

    /**
     * @return ChoiceQuestion
     */
    private function getIntervalContextQuestion()
    {
        $question = new ChoiceQuestion(
            'Please enter the interval context (default is minute):',
            $this->intervalContexts,
            4
        );
        $question->setValidator(
            function ($value) {
                if ( ! array_key_exists($value, $this->intervalContexts)) {
                    throw new \Exception('Please select from the list');
                }

                return $this->intervalContexts[$value];
            }
        );

        return $question;
    }

    /**
     * @return ChoiceQuestion
     */
    private function getPriorityQuestion()
    {
        $question = new ChoiceQuestion(
            'Please enter the priority (1 is high, default is 5):',
            $this->priorities,
            4
        );
        $question->setValidator(
            function ($value) {
                if ( ! array_key_exists($value, $this->priorities)) {
                    throw new \Exception('Please select from the list');
                }

                return $this->priorities[$value];
            }
        );

        return $question;
    }

    /**
     * @return Question
     */
    private function getIntervalQuestion()
    {
        $question = new Question('Please enter the interval:');
        $question->setValidator(
            function ($value) {
                $interval = (int)$value;
                if ((string)$interval !== $value || $interval <= 0) {
                    throw new \Exception('Please enter a positive number');
                }

                return $interval;
            }
        );

        return $question;
    }

    /**
     * @return Question
     */
    private function getStartDateQuestion()
    {
        $question = new Question('Please enter the start date (YYYY-MM-DD HH:MM:SS, default is now):', new \DateTime());
        $question->setValidator(
            function ($value) {
                $value = $value instanceof \DateTime ? $value->format('Y-m-d H:i:s') : $value;
                $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                if ( ! $datetime instanceof \DateTime || $datetime->format('Y-m-d H:i:s') !== $value) {
                    throw new \Exception('The datetime is required to be in format YYYY-MM-DD HH:MM:SS');
                }

                return $datetime;
            }
        );

        return $question;
    }
}