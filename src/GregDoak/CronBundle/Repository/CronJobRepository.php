<?php

namespace GregDoak\CronBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CronJobRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function getRunningCronJobs()
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.end_date IS NULL')
            ->andWhere('c.hostname = :hostname')
            ->setParameter(':hostname', gethostname())
            ->orderBy('c.id', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}