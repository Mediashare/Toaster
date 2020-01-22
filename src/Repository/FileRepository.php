<?php

namespace App\Repository;

use App\Entity\File;
use App\Entity\Hub;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    /**
     * @return File[] Returns an array of Tag objects
     */
    public function findByPage(Hub $hub, int $page, int $max)
    {
        $query = $this->createQueryBuilder('f')
            ->where(':hub = f.hub')
            ->setParameter('hub', $hub)
            ->orderBy('f.updateDate', 'DESC')
            ->getQuery()
            // ->getResult()
        ;

        $results = $this->paginate($query, $page, $max);

        return $results;
    }

    public function paginate($query, $page = 1, $limit = 2)
    {
        $paginator = new Paginator($query);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }

    /**
     * @return File[] Returns an array of Tag objects
     */
    public function findByTag(Tag $tag)
    {
        $results = $this->createQueryBuilder('f')
            ->andWhere(':tag MEMBER OF f.tags')
            ->setParameter('tag', $tag)
            ->orderBy('f.updateDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
        return $results;
    }

    // /**
    //  * @return File[] Returns an array of File objects
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
    public function findOneBySomeField($value): ?File
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
