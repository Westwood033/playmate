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

public function findFiltered(
    string $q = '',
    string $city = '',
    string $status = 'all',
    bool $availableOnly = false
): array {
    $tournaments = $this->findBy([], ['tournamentDate' => 'ASC']);

    $now = new \DateTime();
    $qLower = mb_strtolower($q);
    $cityLower = mb_strtolower($city);

    $tournaments = array_filter($tournaments, function ($t) use (
        $q, $qLower, $city, $cityLower, $status, $availableOnly, $now
    ) {
        if ($q !== '') {
            $name = mb_strtolower($t->getName() ?? '');
            $desc = mb_strtolower($t->getDescription() ?? '');
            if (!str_contains($name, $qLower) && !str_contains($desc, $qLower)) {
                return false;
            }
        }

        if ($city !== '') {
            $parts = explode('|', $t->getAddress() ?? '');
            $tCity = mb_strtolower(trim($parts[2] ?? ''));
            if ($tCity === '' || !str_contains($tCity, $cityLower)) {
                return false;
            }
        }

        $date = $t->getTournamentDate();
        if ($status === 'upcoming' && (!($date instanceof \DateTime) || $date < $now)) {
            return false;
        }
        if ($status === 'past' && (!($date instanceof \DateTime) || $date >= $now)) {
            return false;
        }

        if ($availableOnly) {
            $max = $t->getParticipantNumber();
            if ($max !== null && $t->getUsers()->count() >= $max) {
                return false;
            }
        }

        return true;
    });

    return array_values($tournaments);
}

/**
 * @return string[] Liste des villes (3ᵉ segment de l'adresse) triées alphabétiquement
 */
public function findDistinctCities(): array
{
    $rows = $this->createQueryBuilder('t')
        ->select('t.address')
        ->distinct()
        ->getQuery()
        ->getArrayResult();

    $cities = [];
    foreach ($rows as $row) {
        $parts = explode('|', $row['address'] ?? '');
        $city = trim($parts[2] ?? '');
        if ($city !== '' && !in_array($city, $cities, true)) {
            $cities[] = $city;
        }
    }

    sort($cities, SORT_NATURAL | SORT_FLAG_CASE);
    return $cities;
}
}
