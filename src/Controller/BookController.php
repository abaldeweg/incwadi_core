<?php

/*
 * This script is part of baldeweg/incwadi-core
 *
 * Copyright 2019 André Baldeweg <kontakt@andrebaldeweg.de>
 */

namespace Baldeweg\Controller;

use Baldeweg\Entity\Book;
use Baldeweg\Form\BookType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/book", name="book_")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/{id}", methods={"GET"}, name="show")
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Request $request, Book $book): JsonResponse
    {
        return $this->json($book);
    }

    /**
     * @Route("/new", methods={"POST"}, name="new")
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request): JsonResponse
    {
        $book = new Book();
        $book->setAdded(new \DateTime());
        $form = $this->createForm(BookType::class, $book);

        $form->submit(
            json_decode(
                $request->getContent(),
                true
            )
        );
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->json($book);
        }

        return $this->json([
            'msg' => 'Please enter a valid book!'
        ]);
    }

    /**
     * @Route("/edit/{id}", methods={"POST"}, name="edit")
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, Book $book): JsonResponse
    {
        $form = $this->createForm(BookType::class, $book);

        $form->submit(
            json_decode(
                $request->getContent(),
                true
            )
        );
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->json($book);
        }

        return $this->json([
            'msg' => 'Please enter a valid book!'
        ]);
    }
}