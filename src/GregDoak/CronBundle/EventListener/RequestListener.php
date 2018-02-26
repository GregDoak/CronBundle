<?php

namespace GregDoak\CronBundle\EventListener;

use Doctrine\ORM\EntityManager;
use GregDoak\CronBundle\Service\CronJobService;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class RequestListener
 * @package GregDoak\CronBundle\EventListener
 */
class RequestListener
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /** @var bool $runOnRequest */
    private $runOnRequest;

    /**
     * RequestListener constructor.
     * @param EntityManagerInterface $entityManager
     * @param bool $runOnRequest
     */
    public function __construct(EntityManager $entityManager, bool $runOnRequest)
    {
        $this->entityManager = $entityManager;
        if ( ! $this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
        $this->runOnRequest = $runOnRequest;
    }

    public function runScheduledCronJobCommand()
    {
        if ($this->runOnRequest === true) {
            $cronJobService = new CronJobService($this->entityManager);
            $cronJobService
                ->reserveScheduledTasks()
                ->execute();
        }
    }
}