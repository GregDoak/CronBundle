<?php

namespace GregDoak\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cron_job_logs")
 */
class CronJobLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="GregDoak\CronBundle\Entity\CronJob")
     * @Assert\NotNull(message="The cron job should not be empty")
     * @var CronJob
     */
    private $cronJob;

    /**
     * @ORM\ManyToOne(targetEntity="GregDoak\CronBundle\Entity\CronJobTask")
     * @Assert\NotNull(message="The cron job task should not be empty")
     * @var CronJob
     */
    private $cronJobTask;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     * @Assert\NotNull(message="The output cannot be empty")
     * @var array
     */
    private $output;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="The exit code cannot be empty")
     * @var integer
     */
    private $exitCode;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(message="The start date cannot be empty")
     * @var \DateTime
     */
    private $startedOn;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(message="The end date cannot be empty")
     * @var \DateTime
     */
    private $endedOn;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return CronJob
     */
    public function getCronJob(): CronJob
    {
        return $this->cronJob;
    }

    /**
     * @param CronJob $cronJob
     * @return CronJobLog
     */
    public function setCronJob(CronJob $cronJob): CronJobLog
    {
        $this->cronJob = $cronJob;

        return $this;
    }

    /**
     * @return CronJob
     */
    public function getCronJobTask(): CronJob
    {
        return $this->cronJobTask;
    }

    /**
     * @param CronJobTask $cronJobTask
     * @return CronJobLog
     */
    public function setCronJobTask(CronJobtask $cronJobTask): CronJobLog
    {
        $this->cronJobTask = $cronJobTask;

        return $this;
    }

    /**
     * @return array
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * @param array $output
     * @return CronJobLog
     */
    public function setOutput(array $output): CronJobLog
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * @param int $exitCode
     * @return CronJobLog
     */
    public function setExitCode(int $exitCode): CronJobLog
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartedOn(): \DateTime
    {
        return $this->startedOn;
    }

    /**
     * @param \DateTime $startedOn
     * @return CronJobLog
     */
    public function setStartedOn(\DateTime $startedOn): CronJobLog
    {
        $this->startedOn = $startedOn;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndedOn(): \DateTime
    {
        return $this->endedOn;
    }

    /**
     * @param \DateTime $endedOn
     * @return CronJobLog
     */
    public function setEndedOn(\DateTime $endedOn): CronJobLog
    {
        $this->endedOn = $endedOn;

        return $this;
    }
}