<?php

namespace GregDoak\CronBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="GregDoak\CronBundle\Repository\CronJobRepository")
 * @ORM\Table(name="cron_jobs")
 */
class CronJob
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $hostname;

    /**
     * @ORM\Column(type="integer", length=16)
     * @var integer
     */
    private $pid;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $start_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $end_date;

    /**
     * @ORM\OneToMany(targetEntity="GregDoak\CronBundle\Entity\CronJobTask", mappedBy="cronJob")
     * @var Collection|CronJobTask[]
     */
    private $tasks;

    public function __construct()
    {
        $this->hostname = gethostname();
        $this->pid = getmypid();
        $this->start_date = new \DateTime();
        $this->tasks = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHostName(): string
    {
        return $this->hostname;
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->start_date;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->end_date;
    }

    /**
     * @return CronJob
     */
    public function setEndDate(): CronJob
    {
        $this->end_date = new \DateTime();

        return $this;
    }

    /**
     * @return CronJobTask[]|Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param CronJobTask[]|Collection $tasks
     */
    public function setTasks($tasks): void
    {
        $this->tasks = $tasks;
    }

}
