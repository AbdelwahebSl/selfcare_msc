<?php

namespace App\Repository;

use App\Entity\SelfcareUser;
use App\Entity\WorkshopCart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WorkshopCart|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkshopCart|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkshopCart[]    findAll()
 * @method WorkshopCart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkshopCartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkshopCart::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(WorkshopCart $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(WorkshopCart $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    public function findByFile()
    {

        return $this->createQueryBuilder('w')
            ->select('w')
            ->andwhere('w.file IS NOT NULL')
            ->orderBy('w.id','desc')
            ->getQuery()
            ->getResult();
    }

    public function findByValidateFile(SelfcareUser $user)
    {
        return $this->createQueryBuilder('w')
            ->select('w')
            ->innerJoin('App\Entity\CartFile', 'cartFile', 'WITH', 'w.file = cartFile.id')
            ->where('w.selfcareUer = :user')
            ->andwhere('cartFile.status IN (:status)')
            ->setParameter('status',array ('0','1'))
            ->setParameter('user',$user)
            ->orderBy('w.id','DESC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return WorkshopCart[] Returns an array of WorkshopCart objects
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
    public function findOneBySomeField($value): ?WorkshopCart
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
