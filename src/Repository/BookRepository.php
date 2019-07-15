<?php

/*
 * This script is part of baldeweg/incwadi-core
 *
 * Copyright 2019 André Baldeweg <kontakt@andrebaldeweg.de>
 * MIT-licensed
 */

namespace Incwadi\Core\Repository;

use Incwadi\Core\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    const LIMIT = 20;

    const OFFSET = 0;


    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findDemanded(
        array $criteria,
        string $orderBy='default',
        int $limit = self::LIMIT,
        int $offset = self::OFFSET
    ): array {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('b');
        $qb->from('Baldeweg:Book', 'b');

        $qb->leftJoin('b.author', 'a');

        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('b.stocked', ':stocked'),
                $this->term($qb, $criteria['term']),
                $this->branch($qb, $criteria['branch']),
                $this->added($qb, $criteria['added']),
                $this->genre($qb, $criteria['genre']),
                $this->lending($qb, $criteria['lending']),
                $this->yearOfPublication($qb, $criteria['yearOfPublication']),
                $this->type($qb, $criteria['type'])
            )
        );

        $qb->orderBy($this->orderings()[$orderBy]['field'], $this->orderings()[$orderBy]['direction']);

        $this->setParams($qb, $criteria);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        $query = $qb->getQuery();

        return $query->getResult();
    }

    private function setParams(QueryBuilder $qb, array $criteria)
    {
        $criteria['term'] = preg_replace('/[%\*]/', '', $criteria['term']);
        if ($criteria['term']) {
            $qb->setParameter('term', '%' . $criteria['term'] . '%');
        }

        $qb->setParameter(
            'stocked',
            array_key_exists('stocked', $criteria) ? $criteria['stocked'] : true
        );

        if ($criteria['branch'] !== 'none' && $criteria['branch'] !== 'any') {
            $qb->setParameter(
                'branch',
                explode(',', trim($criteria['branch']))
            );
        }

        if ($criteria['genre'] !== 'none' && $criteria['genre'] !== 'any') {
            $qb->setParameter(
                'genre',
                explode(',', trim($criteria['genre']))
            );
        }

        if ($criteria['lending']) {
            $qb->setParameter(
                'lending',
                new \DateTime('@' . $criteria['lending'])
            );
        }

        if ($criteria['added']) {
            $qb->setParameter(
                'added',
                new \DateTime('@' . $criteria['added'])
            );
        }

        if ($criteria['yearOfPublication']) {
            $qb->setParameter(
                'yearOfPublication',
                (int)$criteria['yearOfPublication']
            );
        }

        if ($criteria['type']) {
            if (!in_array($criteria['type'], Book::TYPES)) {
                $qb->setParameter(
                    'type',
                    null
                );
            }

            $qb->setParameter(
                'type',
                $criteria['type']
            );
        }
    }

    private function term(QueryBuilder $qb, ?string $term)
    {
        if ($term) {
            return $qb->expr()->orX(
                $qb->expr()->like('b.title', ':term'),
                $qb->expr()->like('a.firstname', ':term'),
                $qb->expr()->like('a.lastname', ':term')
            );
        }

        return null;
    }

    private function branch(QueryBuilder $qb, ?string $branch)
    {
        if ($branch === 'none') {
            return $qb->expr()->isNull('b.branch');
        }
        if ($branch === 'any') {
            return $qb->expr()->isNotNull('b.branch');
        }

        return $qb->expr()->in('b.branch', ':branch');
    }

    private function genre(QueryBuilder $qb, ?string $genre)
    {
        if ($genre === 'none') {
            return $qb->expr()->isNull('b.genre');
        }
        if ($genre === 'any') {
            return $qb->expr()->isNotNull('b.genre');
        }

        return $qb->expr()->in('b.genre', ':genre');
    }

    private function lending(QueryBuilder $qb, ?int $lending)
    {
        if ($lending) {
            return $qb->expr()->lte('b.lendOn', ':lending');
        }

        return null;
    }

    private function added(QueryBuilder $qb, ?int $added)
    {
        if ($added) {
            return $qb->expr()->lte('b.added', ':added');
        }

        return null;
    }

    private function yearOfPublication(QueryBuilder $qb, ?int $yearOfPublication)
    {
        if ($yearOfPublication) {
            return $qb->expr()->eq('b.yearOfPublication', ':yearOfPublication');
        }

        return null;
    }

    private function type(QueryBuilder $qb, ?string $type)
    {
        if ($type) {
            return $qb->expr()->lte('b.type', ':type');
        }

        return null;
    }

    private function orderings(): array
    {
        return [
            'asc' => [
                'field' => 'b.id',
                'direction' => 'ASC'
            ],
            'desc' => [
                'field' => 'b.id',
                'direction' => 'DESC'
            ],
            'genre_asc' => [
                'field' => 'b.genre',
                'direction' => 'ASC'
            ],
            'genre_desc' => [
                'field' => 'b.genre',
                'direction' => 'DESC'
            ],
            'added_asc' => [
                'field' => 'b.added',
                'direction' => 'ASC'
            ],
            'added_desc' => [
                'field' => 'b.added',
                'direction' => 'DESC'
            ],
            'title_asc' => [
                'field' => 'b.title',
                'direction' => 'ASC'
            ],
            'title_desc' => [
                'field' => 'b.title',
                'direction' => 'DESC'
            ],
            'author_asc' => [
                'field' => 'a.lastname',
                'direction' => 'ASC'
            ],
            'author_desc' => [
                'field' => 'a.lastname',
                'direction' => 'DESC'
            ],
            'price_asc' => [
                'field' => 'b.price',
                'direction' => 'ASC'
            ],
            'price_desc' => [
                'field' => 'b.price',
                'direction' => 'DESC'
            ],
            'yearOfPublication_asc' => [
                'field' => 'b.yearOfPublication',
                'direction' => 'ASC'
            ],
            'yearOfPublication_desc' => [
                'field' => 'b.yearOfPublication',
                'direction' => 'DESC'
            ],
            'type_asc' => [
                'field' => 'b.type',
                'direction' => 'ASC'
            ],
            'type_desc' => [
                'field' => 'b.type',
                'direction' => 'DESC'
            ],
            'premium_asc' => [
                'field' => 'b.premium',
                'direction' => 'ASC'
            ],
            'premium_desc' => [
                'field' => 'b.premium',
                'direction' => 'DESC'
            ]
        ];
    }
}
