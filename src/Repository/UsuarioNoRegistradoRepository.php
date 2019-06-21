<?php

namespace App\Repository;

use App\Entity\UsuarioNoRegistrado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UsuarioNoRegistrado|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioNoRegistrado|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioNoRegistrado[]    findAll()
 * @method UsuarioNoRegistrado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioNoRegistradoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UsuarioNoRegistrado::class);
    }

    // /**
    //  * @return UsuarioNoRegistrado[] Returns an array of UsuarioNoRegistrado objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UsuarioNoRegistrado
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
