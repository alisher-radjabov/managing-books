<?php

namespace FrontBundle\Controller;

use CommentBundle\Entity\Comment;
use CommentBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SmartlibBundle\Entity\Book;
use SmartlibBundle\Entity\ReadingProgress;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BookController
 * @Route("/book")
 * @package FrontBundle\Controller
 */
class BookController extends Controller
{

    /**
     * @param Request $request
     * @Route("/", name="front_book_index")
     */
    public function indexAction(Request $request)
    {

    }

    /**
     * Get book details
     *
     * @param Request $request
     * @param $id
     * @Route("/book-details/{id}", name="front_book_book_details")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function bookDetailsAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $bookDetails = $em->getRepository('SmartlibBundle:Book')->find($id);
        $authorBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getAuthorBooks($bookDetails->getAuthor());
        $otherBookParts = $em->getRepository('SmartlibBundle:Book')->getFront()->getOtherBookParts($bookDetails->getCollection());
        $bookContentChapter = $em->getRepository('SmartlibBundle:Book')->getFront()->getBookContentChapters($id);
        $bookComments = $em->getRepository('SmartlibBundle:Book')->getFront()->getBookComments($id);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $currentViews = $bookDetails->getViewsFromSite() + 1;
        $bookDetails->setViewsFromSite($currentViews);
        $em->flush();

        $searchService = $this->get('search.service');
        $client = $searchService->getClient();
        $query = $client->createMoreLikeThis();
        $query->setQuery('id:' . $id);
        $query->setMltFields('title,summary');
        $query->setMinimumDocumentFrequency(1);
        $query->setMinimumTermFrequency(1);
        $query->setMatchInclude(true);
        $mltResults = $client->select($query);

        if($this->getUser()){
            /* Writing comment on book */
            if ($form->isSubmitted() && $form->isValid()) {
                $comment->setDocument($bookDetails->getId());
                $comment->setUser($this->getUser()->getId());
                $comment->setCreationDateTime(new \DateTime('now'));

                $em->persist($comment);
                $em->flush();

                return $this->redirectToRoute('front_book_book_details', ['id' => $bookDetails->getId()]);
            }
        }

        $keywords = explode(",", $bookDetails->translate($request->getLocale())->getKeywords());

        return $this->render('FrontBundle:Book:book_details.html.twig', [
            'bookDetails' => $bookDetails,
            'authorBooks' => $authorBooks,
            'otherBookParts' => $otherBookParts,
            'bookContentChapter' => $bookContentChapter,
            'keywords' => $keywords,
            'bookComments' => $bookComments,
            'similarBooks' => $mltResults,
            'form' => $form->createView()
        ]);
    }

    /**
     * Update downloads from site
     *
     * @ParamConverter("book", class="SmartlibBundle:Book")
     * @param Request $request
     * @param Book $book
     * @param $type
     * @Route("/downloaded-from-site/{id}/{type}", name="front_book_downloaded_from_site")
     * @return bool|null|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function updateDownloadFromSiteAction(Request $request, Book $book, $type)
    {

        $fileService = $this->get('file.service');
        $response = null;

        if ('pdf' == $type) {
            $response = $fileService->forceDownloadFile($book->getPdfFile(), $book->translate()->getTitle());
        }

        if ('word' == $type) {
            $response = $fileService->forceDownloadFile($book->getWordFile(), $book->translate()->getTitle());
        }

        if ('epub' == $type) {
            $response = $fileService->forceDownloadFile($book->getEpubFile(), $book->translate()->getTitle());
        }

        $em = $this->getDoctrine()->getManager();
        $book->setDownloadsFromSite($book->getDownloadsFromSite()+1);
        $em->flush();

        return $response;

    }

    /**
     * Add book to user favorites
     *
     * @param Request $request
     * @Route("/add-favorite/{id}", name="front_book_add_favorite")
     * @return string|JsonResponse
     */
    public function addFavoriteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AdminBundle:User')->find(32);

        $user = $em->getRepository('AdminBundle:User')->find($user);
        $book = $em->getRepository('SmartlibBundle:Book')->find($id);

        $book->addFan($user);

        $em->persist($book);
        $em->flush();

        return $this->redirectToRoute('front_user_details', ['id' => $user->getId()]);

    }

    /**
     * Delete book from user favorites
     *
     * @param Request $request
     * @param $id
     * @Route("/remove-favorite/{id}", name="front_book_remove_favorite")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeFavoriteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AdminBundle:User')->find(32);

        $book = $em->getRepository('SmartlibBundle:Book')->find($id);
        $book->removeFan($user);

        $em->persist($book);
        $em->flush();

        return $this->redirectToRoute('front_main_index');

    }

    /**
     * Read book
     *
     * @param $id
     * @param $slug
     * @Route("/read/{id}/{slug}", name="front_book_read")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function readBookAction($id, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $bookDetails = $em->getRepository('SmartlibBundle:Book')->find($id);
        $bookContent = $em->getRepository('SmartlibBundle:Book')->getFront()->getBookContent($id, $slug);
        $bookContentChapters = $em->getRepository('SmartlibBundle:Book')->getFront()->getBookContentChapters($id);

        $user = $em->getRepository('AdminBundle:User')->find(32);

        $alreadyReading = $em->getRepository('SmartlibBundle:ReadingProgress')->findBy(array('book' => $bookDetails, 'user' => $user));

        if(!$alreadyReading)
        {
            $readingProgress = new ReadingProgress();
            $readingProgress->setBook($bookDetails);
            $readingProgress->setUser($user);
            $readingProgress->setPercent(1);

            $em->persist($readingProgress);
            $em->flush();
        }

        return $this->render('FrontBundle:Book:read_book.html.twig', [
            'bookDetails'           => $bookDetails,
            'bookContent'           => $bookContent,
            'bookContentChapters'   => $bookContentChapters
        ]);

    }
}