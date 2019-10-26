<?php

namespace App\Repository;

use App\Entity\Ping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Ping|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ping|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ping[]    findAll()
 * @method Ping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ping::class);
    }

    public function deletePingsOfOneDevice($deviceId)
    {
        $deletePings = $this->createQueryBuilder("ping")
            ->delete()
            ->where('ping.device  = :deviceId')->setParameter("deviceId", $deviceId)
            ->getQuery()->execute();

        return $deletePings;
    }

    // /**
    //  * @return Ping[] Returns an array of Ping objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ping
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
