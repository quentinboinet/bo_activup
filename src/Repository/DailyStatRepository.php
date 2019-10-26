<?php

namespace App\Repository;

use App\Entity\DailyStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DailyStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyStat[]    findAll()
 * @method DailyStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyStatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DailyStat::class);
    }


    public function deleteDailyStatsOfOneDevice($deviceId)
    {
        $deleteDailyStats = $this->createQueryBuilder("dailyStats")
            ->delete()
            ->where('dailyStats.device  = :deviceId')->setParameter("deviceId", $deviceId)
            ->getQuery()->execute();

        return $deleteDailyStats;
    }

    // /**
    //  * @return DailyStat[] Returns an array of DailyStat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DailyStat
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
