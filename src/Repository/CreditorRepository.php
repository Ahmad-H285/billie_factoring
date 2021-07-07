<?php

namespace App\Repository;

use App\Entity\Creditor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Creditor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Creditor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Creditor[]    findAll()
 * @method Creditor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreditorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creditor::class);
    }

    // /**
    //  * @return Creditor[] Returns an array of Creditor objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Creditor
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
