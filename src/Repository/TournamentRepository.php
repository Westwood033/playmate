<?php

namespace App\Repository;

use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tournament>
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

//    /**
//     * @return Tournament[] Returns an array of Tournament objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Tournament
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


public function findAllInWeek(\DateTimeInterface $datetime): array
{
    $startOfWeek = (clone $datetime)->modify('monday this week')->setTime(0, 0, 0);
    $endOfWeek = (clone $startOfWeek)->modify('+6 days')->setTime(23, 59, 59);

    return $this->createQueryBuilder('t')
        ->andWhere('t.tournamentDate BETWEEN :start AND :end')
        ->setParameter('start', $startOfWeek)
        ->setParameter('end', $endOfWeek)
        ->orderBy('t.tournamentDate', 'ASC')
        ->setMaxResults(6)
        ->getQuery()
        ->getResult();
}
}
