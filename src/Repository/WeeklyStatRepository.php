<?php

namespace App\Repository;

use App\Entity\WeeklyStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WeeklyStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeeklyStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeeklyStat[]    findAll()
 * @method WeeklyStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeeklyStatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WeeklyStat::class);
    }

    public function deleteWeeklyStatsOfOneDevice($deviceId)
    {
        $deleteWeeklyStats = $this->createQueryBuilder("weeklyStats")
            ->delete()
            ->where('weeklyStats.device  = :deviceId')->setParameter("deviceId", $deviceId)
            ->getQuery()->execute();

        return $deleteWeeklyStats;
    }

    // /**
    //  * @return WeeklyStat[] Returns an array of WeeklyStat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WeeklyStat
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
