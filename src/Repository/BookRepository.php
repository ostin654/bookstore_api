<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\BookTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function searchByName(string $searchText)
    {
        $queryBuilder = $this->createQueryBuilder('book')
            ->select('book')
            ->distinct()
            ->join(BookTranslation::class, 'bookTranslation', Join::WITH, 'bookTranslation.translatable = book')
            ->andWhere('bookTranslation.name LIKE :searchText')
                ->setParameter('searchText', "%{$searchText}%");

        return $queryBuilder->getQuery()->getResult();
    }
}
