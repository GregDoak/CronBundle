<?php

namespace GregDoak\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="GregDoak\CronBundle\Repository\CronJobTaskRepository")
 * @ORM\Table(name="cron_job_tasks",indexes={@ORM\Index(columns={"cron_job_id", "next_run", "active"})})
 */
class CronJobTask
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="GregDoak\CronBundle\Entity\CronJob", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=true)
     * @var CronJob|null
     */
    private $cronJob;

    /**
     * @ORM\Column(type="string", length=1024)
     * @Assert\NotBlank(message="The command should not be empty")
     * @var string
     */
    private $command;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="The start date should not be empty")
     * @var \DateTime
     */
    private $startDate;

    /**
     * @ORM\Column(type="integer", length=8)
     * @Assert\NotBlank(message="The interval period should not be empty")
     * @var int
     */
    private $intervalPeriod;

    /**
     * @ORM\Column(type="string", length=8)
     * @Assert\NotBlank(message="The interval context should not be empty")
     * @var string
     */
    private $intervalContext;

    /**
     * @ORM\Column(type="integer", length=2)
     * @Assert\NotBlank(message="The priority should not be empty")
     * @var int
     */
    private $priority;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="The next run should not be empty")
     * @var \DateTime
     */
    private $nextRun;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $lastRun;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="The created on should not be empty")
     * @var \DateTime
     */
    private $createdOn;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedOn;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotNull(message="The active should not be empty")
     * @var boolean
     */
    private $active;

    public function __construct()
    {
        $this->createdOn = new \DateTime();
        $this->active = true;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return CronJob|null
     */
    public function getCronJob(): ?CronJob
    {
        return $this->cronJob;
    }

    /**
     * @param CronJob|null $cronJob
     * @return CronJobTask
     */
    public function setCronJob(?CronJob $cronJob): CronJobTask
    {
        $this->cronJob = $cronJob;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return CronJobTask
     */
    public function setCommand(string $command): CronJobTask
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return CronJobTask
     */
    public function setStartDate(\DateTime $startDate): CronJobTask
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getIntervalPeriod(): int
    {
        return $this->intervalPeriod;
    }

    /**
     * @param int $intervalPeriod
     * @return CronJobTask
     */
    public function setIntervalPeriod(int $intervalPeriod): CronJobTask
    {
        $this->intervalPeriod = $intervalPeriod;

        return $this;
    }

    /**
     * @return string
     */
    public function getIntervalContext(): string
    {
        return $this->intervalContext;
    }

    /**
     * @param string $intervalContext
     * @return CronJobTask
     */
    public function setIntervalContext(string $intervalContext): CronJobTask
    {
        $this->intervalContext = $intervalContext;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return CronJobTask
     */
    public function setPriority(int $priority): CronJobTask
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNextRun(): \DateTime
    {
        return $this->nextRun;
    }

    /**
     * @return CronJobTask
     */
    public function setNextRun(): CronJobTask
    {
        $interval = \DateInterval::createFromDateString(
            sprintf('%d %s', $this->getIntervalPeriod(), $this->getIntervalContext())
        );
        $nextRun = $this->nextRun === null ? $this->getStartDate() : new \DateTime();

        $this->nextRun = $nextRun->add($interval);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastRun(): \DateTime
    {
        return $this->lastRun;
    }

    /**
     * @return CronJobTask
     */
    public function setLastRun(): CronJobTask
    {
        $this->lastRun = new \DateTime();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedOn(): \DateTime
    {
        return $this->updatedOn;
    }

    /**
     * @return CronJobTask
     */
    public function setUpdatedOn(): CronJobTask
    {
        $this->updatedOn = new \DateTime();

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return CronJobTask
     */
    public function setActive(bool $active): CronJobTask
    {
        $this->active = $active;

        return $this;
    }
}
