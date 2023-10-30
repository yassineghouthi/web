<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request ;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
//    #[Route('/all/book', name: 'list_book')]
//    public function listBooks( $name,BookRepository $repository): Response
//    {
//        $books = $repository->findPublishedBook($name);
//        return $this->render('book/list.html.twig', ['books' => $books]);
//    }
    #[Route('/all/book', name: 'list_book')]
    public function listBook( Request $request ,BookRepository $repository): Response
    {
//        $book= $repository->find($id);
//        $book->getAuthor()->setNbBooks($book->getAuthor()->getNBBooks() +1);
        $ref = $request->query->get('ref');
        $books = $repository->findBookbyref($ref);
        if (!$books){
            $books = $repository->findAll();
        }
        return $this->render('book/list.html.twig', ['books' => $books]);
    }


    #[Route('/add/book', name: 'add_book')]
    public function addBook(Request $request ,AuthorRepository $repository,EntityManagerInterface $managerRegistry):Response
    {
        $book= new Book();
        $book->setPublished(0);
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            $nbbook= $book->getAuthor()->getNbBooks();
//            $book->getAuthor()->setNbBooks($nbbook+1);
            $nameauthor = $book->getAuthor();
            $author = $repository->findOneBy(['username' =>  trim($nameauthor)]);
            if ($author){
                $author->setNbBooks($author->getNbBooks() + 1);
                $managerRegistry->persist($author);
           }
            $managerRegistry->persist($book);
            $managerRegistry->flush();
            return $this->redirectToRoute('list_book');
        }
        return $this->render('book/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/update/book/{ref}', name: 'update_book_ref')]

    public function updateBookByRef(Request $request,BookRepository $repository ,int $ref ,  ManagerRegistry $managerRegistry): Response
    {
        $em= $managerRegistry->getManager();
        $book = $repository->find($ref);
        if (!$book){
            return $this->render('book/error.html.twig');
        }
        $book->setPublished(0);
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('list_book');
        }

        return $this->render('book/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/update/book', name: 'update_book')]

    public function updateBook(Request $request,BookRepository $repository  ,  ManagerRegistry $managerRegistry): Response
    {
        $book = $repository->modifyBooksCategory();
        if (!$book){
            return $this->render('book/error.html.twig');
        }
        return $this->redirectToRoute('list_book');

    }

    #[Route('/delete/book/{ref}', name: 'delete_book')]

    public function delete(Request $request,BookRepository $repository ,int $ref ,  ManagerRegistry $managerRegistry): Response
    {
        $book = $repository ->deleteBooksByRef($ref);
        if (!$book){
            return $this->render('book/error.html.twig');
        }
//        $em=$managerRegistry->getManager();
//        $em->remove($book);
//        $em->flush();
        return $this->redirecttoRoute("list_book");

    }
    #[Route('/book/details/{ref}', name: 'details_book')]
    public function detailsBooks(int $ref ,BookRepository $repository): Response
    {
        $book = $repository->find($ref);

       if (!$book){
           return $this->render('book/error.html.twig');
       }
        return $this->render('book/details.html.twig', ['book' => $book]);
    }


}
