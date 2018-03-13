<?php

namespace GregDoak\CronBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CronJobRepository
 * @package GregDoak\CronBundle\Repository
 */
class CronJobRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function getCronJobHistory()
    {
        $query = $this->createQueryBuilder('c')
            ->innerJoin('c.tasks', 'tasks')
            ->orderBy('c.id', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return mixed
     */
    public function getRunningCronJobs()
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.endDate IS NULL')
            ->andWhere('c.hostname = :hostname')
            ->setParameter(':hostname', gethostname())
            ->orderBy('c.id', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}