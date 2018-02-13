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

class UpdateCronJobTaskCommand extends Command
{
    private $intervalContexts = ['year', 'month', 'day', 'hour', 'minute', 'second'];
    private $priorities = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    /** @var array $choiceSelection */
    private $choiceSelection = [];
    /** @var CronJobTask $cronJobTask */
    private $cronJobTask;
    /** @var CronJobTask[] $cronJobTasks */
    private $cronJobTasks = [];
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /** @var ValidatorInterface $validator */
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->validator = $validator;

        $this->cronJobTasks = $this->entityManager->getRepository('GregDoakCronBundle:CronJobTask')->findBy(
            [],
            ['command' => 'ASC']
        );

        foreach ($this->cronJobTasks as $cronJobTask) {
            $this->choiceSelection[] = sprintf(
                'Command: %s - Interval: %d %s - Active: %s',
                $cronJobTask->getCommand(),
                $cronJobTask->getIntervalPeriod(),
                $cronJobTask->getIntervalContext(),
                $cronJobTask->isActive() ? 'Yes' : 'No'
            );
        }
    }

    protected function configure(): void
    {
        $this
            ->setName('cron:update')
            ->setDescription('Update a cron job.')
            ->setDefinition(
                [
                    new InputArgument('cron_task_id', InputArgument::REQUIRED, 'The command to update'),
                    new InputArgument('cron_command', InputArgument::REQUIRED, 'The command to update'),
                    new InputArgument('interval', InputArgument::REQUIRED, 'The interval between runs'),
                    new InputArgument('context', InputArgument::REQUIRED, 'The context of the interval'),
                    new InputArgument('priority', InputArgument::REQUIRED, 'The run priority of the job'),
                    new InputArgument('active', InputArgument::REQUIRED, 'The actice status of the job'),
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
        $this->cronJobTask
            ->setCommand($input->getArgument('cron_command'))
            ->setIntervalPeriod($input->getArgument('interval'))
            ->setIntervalContext($input->getArgument('context'))
            ->setPriority($input->getArgument('priority'))
            ->setActive($input->getArgument('active'));

        $errors = $this->validator->validate($this->cronJobTask);
        foreach ($errors as $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));
        }

        if (\count($errors) === 0) {
            try {

                $this->entityManager->persist($this->cronJobTask);
                $this->entityManager->flush();
                $output->writeln(sprintf('Updated Cron Job <comment>%s</comment>', $this->cronJobTask->getCommand()));

            } catch (\Exception $exception) {
                $output->writeln('<error>Failed to update cron job to the database</error>');
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $answer = $this->getHelper('question')->ask($input, $output, $this->getCommandIdQuestion());
        $input->setArgument('cron_task_id', $answer);

        $output->writeln('<comment>Leave questions blank to use default settings.</comment>');

        $questions = [
            'cron_command' => $this->getCommandQuestion(),
            'interval' => $this->getIntervalQuestion(),
            'context' => $this->getIntervalContextQuestion(),
            'priority' => $this->getPriorityQuestion(),
            'active' => $this->getActiveQuestion(),
        ];

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    /**
     * @return Question
     */
    private function getActiveQuestion()
    {
        $question = new ChoiceQuestion('Is command active:', ['Yes', 'No'], (int) ! $this->cronJobTask->isActive());
        $question->setValidator(
            function ($value) {
                return ! (bool)$value;
            }
        );

        return $question;
    }

    /**
     * @return Question
     */
    private function getCommandIdQuestion()
    {
        $question = new ChoiceQuestion('Please enter the command to be executed:', $this->choiceSelection);
        $question->setValidator(
            function ($value) {
                if ( ! array_key_exists($value, $this->cronJobTasks)) {
                    throw new \Exception('Please select from the list');
                }

                $this->cronJobTask = $this->cronJobTasks[$value];
            }
        );

        return $question;
    }

    /**
     * @return Question
     */
    private function getCommandQuestion()
    {
        $question = new Question('Please enter the command to be executed:', $this->cronJobTask->getCommand());
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
            'Please enter the interval context:',
            $this->intervalContexts,
            array_search($this->cronJobTask->getIntervalContext(), $this->intervalContexts, true)
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
            'Please enter the priority:', $this->priorities, $this->cronJobTask->getPriority() - 1
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
        $question = new Question('Please enter the interval:', $this->cronJobTask->getIntervalPeriod());
        $question->setValidator(
            function ($value) {
                if ((int)$value <= 0) {
                    throw new \Exception('Please enter a positive number');
                }

                return $value;
            }
        );

        return $question;
    }
}