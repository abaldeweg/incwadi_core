<?php

/*
 * This script is part of baldeweg/incwadi-core
 *
 * Copyright 2019 André Baldeweg <kontakt@andrebaldeweg.de>
 * MIT-licensed
 */

namespace Baldeweg\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Baldeweg\Repository\LendRepository")
 */
class Lend implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Baldeweg\Entity\Customer", inversedBy="lends")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="Baldeweg\Entity\Book", inversedBy="lend", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $book;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lendOn;


    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'user' => $this->getUser(),
            'book' => $this->getBook(),
            'lendOn' => $this->getLendOn()
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Customer
    {
        return $this->user;
    }

    public function setUser(?Customer $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getLendOn(): ?\DateTimeInterface
    {
        return $this->lendOn;
    }

    public function setLendOn(\DateTimeInterface $lendOn): self
    {
        $this->lendOn = $lendOn;

        return $this;
    }
}