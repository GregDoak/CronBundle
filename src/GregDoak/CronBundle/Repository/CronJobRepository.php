<?php

namespace GregDoak\CronBundle\Repository;

use GregDoak\CronBundle\Entity\CronJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CronJobRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CronJob::class);
    }

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