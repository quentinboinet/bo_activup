<?php

namespace App\Repository;

use App\Entity\SessionStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionStat[]    findAll()
 * @method SessionStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionStatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionStat::class);
    }


    public function deleteSessionStatsOfOneDevice($deviceId)
    {
        $deleteSessionStats = $this->createQueryBuilder("sessionStats")
            ->delete()
            ->where('sessionStats.device  = :deviceId')->setParameter("deviceId", $deviceId)
            ->getQuery()->execute();

        return $deleteSessionStats;
    }

    // /**
    //  * @return SessionStat[] Returns an array of SessionStat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SessionStat
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
