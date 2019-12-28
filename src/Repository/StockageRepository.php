<?php

namespace App\Repository;

use App\Entity\Stockage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Stockage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stockage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stockage[]    findAll()
 * @method Stockage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stockage::class);
    }

    // /**
    //  * @return Stockage[] Returns an array of Stockage objects
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
    public function findOneBySomeField($value): ?Stockage
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
