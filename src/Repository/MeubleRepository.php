<?php

namespace App\Repository;

use App\Entity\Meuble;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MeubleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meuble::class);
    }

    public function findByFilters(string $search = '', string $categorieId = ''): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.categorie', 'c')
            ->addSelect('c')
            ->where('m.isActive = true');

        if ($search !== '') {
            $qb->andWhere('m.nom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($categorieId !== '') {
            $qb->andWhere('c.id = :categorieId')
               ->setParameter('categorieId', $categorieId);
        }

        return $qb->orderBy('m.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}