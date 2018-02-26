<?php

namespace GregDoak\CronBundle\Service;

use GregDoak\CronBundle\Entity\CronJob;
use GregDoak\CronBundle\Entity\CronJobLog;
use GregDoak\CronBundle\Entity\CronJobTask;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CronJobService
 * @package GregDoak\CronBundle\Service
 */
class CronJobService
{
    /** @var CronJob $cronJob */
    private $cronJob;
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /**@var \GregDoak\CronBundle\Repository\CronJobTaskRepository|\Doctrine\Common\Persistence\ObjectRepository */
    private $repository;

    /**
     * ExecuteCommandService constructor.
     * @param EntityManagerInterface $entityManager
     * @param CronJob $cronJob
     */
    public function __construct(EntityManagerInterface $entityManager, CronJob $cronJob = null)
    {
        $this->entityManager = $entityManager;
        if ( ! $this->entityManager->isOpen()) {
            $this->entityManager->create(
                $this->entityManager->getConfiguration(),
                $this->entityManager->getConfiguration()
            );
        }
        $this->repository = $this->entityManager->getRepository('GregDoakCronBundle:CronJobTask');
        $this->cronJob = $cronJob === null ? $this->generateCronJob() : $cronJob;
    }

    /**
     * @return $this
     */
    private function closeCronJob(): CronJobService
    {
        $this->cronJob->setEndDate();

        $this->entityManager->persist($this->cronJob);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * @param CronJobLog $cronJobLog
     * @param array $result
     * @param int $exitCode
     */
    private function endLog(CronJobLog $cronJobLog, array $result, int $exitCode): void
    {
        $cronJobLog
            ->setOutput($result)
            ->setExitCode($exitCode)
            ->setEndedOn(new \DateTime());

        $this->entityManager->persist($cronJobLog);
        $this->entityManager->flush();
    }

    /**
     * @return CronJob
     */
    private function generateCronJob(): CronJob
    {
        $cronJob = new CronJob();

        $this->entityManager->persist($cronJob);
        $this->entityManager->flush();

        return $cronJob;
    }

    /**
     * @return mixed
     */
    private function getTasks()
    {
        return $this->repository->getTasksByCronJob($this->cronJob);
    }

    private function releaseTasks(): void
    {
        $this->repository->releaseTasks($this->cronJob);
    }

    /**
     * @param CronJobTask $cronJobTask
     */
    private function scheduleNextTask(CronJobTask $cronJobTask): void
    {
        $cronJobTask
            ->setLastRun()
            ->setNextRun();

        $this->entityManager->persist($cronJobTask);
        $this->entityManager->flush();
    }

    public function closeAndRelease(): void
    {
        $this
            ->closeCronJob()
            ->releaseTasks();
    }

    /**
     * @param CronJobTask $cronJobTask
     * @return CronJobLog
     */
    private function startLog(CronJobTask $cronJobTask): CronJobLog
    {
        $cronJobLog = new CronJobLog();
        $cronJobLog
            ->setCronJob($this->cronJob)
            ->setCronJobTask($cronJobTask)
            ->setStartedOn(new \DateTime());

        return $cronJobLog;
    }

    /**
     * @return $this
     */
    public function reserveScheduledTasks(): CronJobService
    {
        $this->repository->reserveScheduledTasks($this->cronJob);
        $this->cronJob->setTasks($this->getTasks());

        return $this;
    }

    public function reserveTask(CronJobTask $cronJobTask)
    {
        $cronJobTask->setCronJob($this->cronJob);

        $this->entityManager->persist($cronJobTask);
        $this->entityManager->flush();

        $this->cronJob->setTasks([$cronJobTask]);

        return $this;
    }

    public function execute(): void
    {
        foreach ($this->cronJob->getTasks() as $cronJobTask) {
            $cronJobLog = $this->startLog($cronJobTask);
            $result = null;
            $exitCode = 0;
            exec($cronJobTask->getCommand(), $result, $exitCode);
            $this->scheduleNextTask($cronJobTask);
            $this->endLog($cronJobLog, $result, $exitCode);
        }

        $this
            ->closeCronJob()
            ->releaseTasks();
    }
}