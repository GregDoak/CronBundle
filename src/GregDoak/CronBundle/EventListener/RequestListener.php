<?php

namespace GregDoak\CronBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use GregDoak\CronBundle\Service\CronJobService;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListener
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function runScheduledCronJobCommand()
    {
        $cronJobService = new CronJobService($this->entityManager);
        $cronJobService
            ->reserveScheduledTasks()
            ->execute();
    }
}