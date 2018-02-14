<?php

namespace GregDoak\CronBundle\Repository;

use Doctrine\ORM\EntityRepository;
use GregDoak\CronBundle\Entity\CronJob;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CronJobTaskRepository
 * @package GregDoak\CronBundle\Repository
 */
class CronJobTaskRepository extends EntityRepository
{
    public function getActiveTasks()
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.active = :active')
            ->setParameter(':active', true)
            ->orderBy('t.command', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param CronJob $cronJob
     * @return mixed
     */
    public function getTasksByCronJob(CronJob $cronJob)
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.cronJob = :cronJob')
            ->setParameter(':cronJob', $cronJob)
            ->orderBy('t.priority', 'ASC')
            ->addOrderBy('t.command', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param CronJob $cronJob
     * @return mixed
     */
    public function releaseTasks(CronJob $cronJob)
    {
        $query = $this->createQueryBuilder('t')
            ->update('GregDoakCronBundle:CronJobTask', 't')
            ->set('t.cronJob', ':clear')
            ->where('t.cronJob = :cronJob')
            ->setParameter(':clear', null)
            ->setParameter(':cronJob', $cronJob)
            ->getQuery();

        return $query->execute();
    }

    /**
     * @param CronJob $cronJob
     * @return mixed
     */
    public function reserveScheduledTasks(CronJob $cronJob)
    {
        $query = $this->createQueryBuilder('t')
            ->update('GregDoakCronBundle:CronJobTask', 't')
            ->set('t.cronJob', ':cronJob')
            ->where('t.cronJob IS NULL')
            ->andWhere('t.active = :active')
            ->andWhere('t.nextRun < :now')
            ->setParameter(':cronJob', $cronJob)
            ->setParameter(':active', true)
            ->setParameter(':now', new \DateTime())
            ->getQuery();

        return $query->execute();
    }
}