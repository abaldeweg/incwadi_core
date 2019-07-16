<?php

/*
 * This script is part of baldeweg/incwadi-core
 *
 * Copyright 2019 André Baldeweg <kontakt@andrebaldeweg.de>
 * MIT-licensed
 */

namespace Incwadi\Core\Util;

use Incwadi\Core\Entity\Author;
use Incwadi\Core\Entity\Customer;
use Incwadi\Core\Entity\Branch;
use Incwadi\Core\Entity\Genre;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class Export implements ExportInterface
{
    public function export(array $data): string
    {
        $formatDate = function ($object) {
            return $object instanceof \DateTime ? $object->format(\DateTime::ISO8601) : '';
        };
        $formatAuthor = function ($object) {
            return $object instanceof Author ? ['firstname' => $object->getFirstname(), 'surname' => $object->getSurname()] : ['firstname' => null, 'surname' => null];
        };
        $formatLendTo = function ($object) {
            return $object instanceof Customer ? $object->getName() : null;
        };
        $formatBranch = function ($object) {
            return $object instanceof Branch ? $object->getName() : null;
        };
        $formatGenre = function ($object) {
            return $object instanceof Genre ? $object->getName() : null;
        };

        $defaultContext = [
            AbstractNormalizer::CALLBACKS => [
                'added' => $formatDate,
                'lendOn' => $formatDate,
                'author' => $formatAuthor,
                'lendTo' => $formatLendTo,
                'branch' => $formatBranch,
                'genre' => $formatGenre
            ],
        ];

        $serializer = new Serializer(
            [new GetSetMethodNormalizer(null, null, null, null, null, $defaultContext)],
            [new CsvEncoder()]
        );

        return $serializer->serialize($data, 'csv', [
            'csv_delimiter' => ';',
            'ignored_attributes' => ['id']
        ]);
    }
}
