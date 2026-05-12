<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * Recherche filtrée par mot-clé, catégorie, état, statut vendu.
     *
     * @param array{q: string, category: string, condition: string, sold: string} $filters
     * @return Item[]
     */
    public function search(array $filters): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.owner', 'u')
            ->addSelect('u')
            ->orderBy('i.createdAt', 'DESC');

        if (!empty($filters['q'])) {
            $qb->andWhere('i.name LIKE :q OR i.description LIKE :q')
                ->setParameter('q', '%' . $filters['q'] . '%');
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('i.category = :category')
                ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['condition'])) {
            $qb->andWhere('i.condition = :condition')
                ->setParameter('condition', $filters['condition']);
        }

        if ($filters['sold'] !== '') {
            $qb->andWhere('i.isSold = :sold')
                ->setParameter('sold', (bool)$filters['sold']);
        }

        return $qb->getQuery()->getResult();
    }
}
