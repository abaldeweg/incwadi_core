<?php

/*
 * This script is part of incwadi/core
 */

namespace Incwadi\Core\Controller;

use Incwadi\Core\Entity\Book;
use Incwadi\Core\Form\BookCoverType;
use Incwadi\Core\Form\BookType;
use Incwadi\Core\Service\CoverRemove;
use Incwadi\Core\Service\CoverShow;
use Incwadi\Core\Service\CoverUpload;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/book")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/find", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function find(Request $request): JsonResponse
    {
        return $this->json(
            $this
                ->getDoctrine()
                ->getRepository(Book::class)
                ->findDemanded(
                    json_decode(
                        $request->query->get('options'),
                        true
                    )
                )
        );
    }

    /**
     * @Route("/clean", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function clean(): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository(Book::class)->deleteBooksByBranch(
            $this->getUser()->getBranch()
        );
        $em->flush();

        return $this->json(['msg' => 'Cleaned up successfully!']);
    }

    /**
     * @Route("/{id}", methods={"GET"})
     * @Security("is_granted('ROLE_USER') and book.getBranch() === user.getBranch() or is_granted('ROLE_ADMIN')")
     */
    public function show(Request $request, Book $book): JsonResponse
    {
        return $this->json($book);
    }

    /**
     * @Route("/new", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();

        $book = new Book();
        $book->setBranch(
            $this->getUser()->getBranch()
        );
        $form = $this->createForm(BookType::class, $book);

        $form->submit(
            json_decode(
                $request->getContent(),
                true
            )
        );

        $existingBook = $em->getRepository(Book::class)->findDuplicate($book);
        if (null !== $existingBook) {
            return $this->json([
                'msg' => 'Book not saved, because it exists already!',
            ], 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($book);
            $em->flush();

            return $this->json($book);
        }

        return $this->json([
            'msg' => 'Please enter a valid book!',
        ], 400);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     * @Security("is_granted('ROLE_USER') and user.getBranch() === book.getBranch()")
     */
    public function edit(Request $request, Book $book): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(BookType::class, $book);

        $form->submit(
            json_decode(
                $request->getContent(),
                true
            )
        );

        $existingBook = $em->getRepository(Book::class)->findDuplicate($book);
        if (null !== $existingBook) {
            if ($existingBook->getId() !== $book->getId()) {
                return $this->json([
                'msg' => 'Book not saved, because it exists already!',
                ], 409);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // sold
            if (true === $book->getSold() && null === $book->getSoldOn()) {
                $book->setSoldOn(new \DateTime());
            }
            // revert sold
            if (false === $book->getSold() && null !== $book->getSoldOn()) {
                $book->setSoldOn(null);
            }
            // removed
            if (true === $book->getRemoved() && null === $book->getRemovedOn()) {
                $book->setRemovedOn(new \DateTime());
            }
            // revert removed
            if (false === $book->getRemoved() && null !== $book->getRemovedOn()) {
                $book->setRemovedOn(null);
            }
            // reserved
            if (true === $book->getReserved() && null === $book->getReservedAt()) {
                $book->setReservedAt(new \DateTime());
            }
            // revert reserved
            if (false === $book->getReserved() && null !== $book->getReservedAt()) {
                $book->setReservedAt(null);
            }
            $em->flush();

            return $this->json($book);
        }

        return $this->json([
            'msg' => 'Please enter a valid book!',
        ]);
    }

    /**
     * @Route("/cover/{id}", methods={"GET"})
     * @Security("is_granted('ROLE_USER') and user.getBranch() === book.getBranch()")
     */
    public function showCover(Request $request, Book $book, CoverShow $cover): JsonResponse
    {
        return $this->json($cover->show($book));
    }

    /**
     * @Route("/cover/{id}", methods={"POST"})
     * @Security("is_granted('ROLE_USER') and user.getBranch() === book.getBranch()")
     */
    public function cover(Request $request, Book $book, CoverUpload $coverUpload): JsonResponse
    {
        $form = $this->createForm(BookCoverType::class, $book);
        $form->submit(['cover' => $request->files->get('cover')]);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('cover')->getData();
            $coverUpload->upload($book, $file);

            return $this->json($book);
        }

        return $this->json(['msg' => 'Could not upload image.'], 500);
    }

    /**
     * @Route("/cover/{id}", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER') and user.getBranch() === book.getBranch()")
     */
    public function deleteCover(Request $request, Book $book, CoverRemove $cover): JsonResponse
    {
        $cover->remove($book);

        return $this->json(['msg' => 'Cover was deleted.']);
    }

    /**
     * @Route("/sell/{id}", methods={"PUT"})
     * @Security("is_granted('ROLE_USER') and user.getBranch() === book.getBranch()")
     */
    public function sell(Book $book): JsonResponse
    {
        $book->setSold(!$book->getSold());
        $book->setSoldOn(null === $book->getSoldOn() ? new \DateTime() : null);
        $this->getDoctrine()->getManager()->flush();

        return $this->json($book);
    }

    /**
     * @Route("/remove/{id}", methods={"PUT"})
     * @Security("is_granted('ROLE_USER') and user.getBranch() === book.getBranch()")
     */
    public function remove(Book $book): JsonResponse
    {
        $book->setRemoved(!$book->getRemoved());
        $book->setRemovedOn(
            null === $book->getRemovedOn() ? new \DateTime() : null
        );
        $this->getDoctrine()->getManager()->flush();

        return $this->json($book);
    }

    /**
     * @Route("/reserve/{id}", methods={"PUT"})
     * @Security("is_granted('ROLE_USER') and user.getBranch() === book.getBranch()")
     */
    public function reserve(Book $book): JsonResponse
    {
        $book->setReserved(!$book->getReserved());
        $book->setReservedAt(
            null === $book->getReservedAt() ? new \DateTime() : null
        );
        $this->getDoctrine()->getManager()->flush();

        return $this->json($book);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN') and user.getBranch() === book.getBranch()")
     */
    public function delete(Book $book, CoverRemove $cover): JsonResponse
    {
        $cover->remove($book);

        $em = $this->getDoctrine()->getManager();
        $em->remove($book);
        $em->flush();

        return $this->json([
            'msg' => 'The book was successfully deleted.',
        ]);
    }
}
