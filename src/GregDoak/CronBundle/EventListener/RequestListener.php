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
    /** @var string $projectDirectory */
    private $projectDirectory;
    /** @var bool $runOnRequest */
    private $runOnRequest;

    /**
     * RequestListener constructor.
     * @param EntityManager $entityManager
     * @param string $projectDirectory
     * @param bool $runOnRequest
     * @throws \Doctrine\ORM\ORMException
     */
    public function __construct(EntityManager $entityManager, string $projectDirectory, bool $runOnRequest)
    {
        $this->entityManager = $entityManager;
        if ( ! $this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
        $this->projectDirectory = $projectDirectory;
        $this->runOnRequest = $runOnRequest;
    }

    public function runScheduledCronJobCommand()
    {
        if ($this->runOnRequest === true) {
            $cronJobService = new CronJobService($this->entityManager, $this->projectDirectory);
            $cronJobService
                ->reserveScheduledTasks()
                ->execute();
        }
    }
}