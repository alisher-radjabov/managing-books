<?php

namespace FrontBundle\Controller;

use SmartlibBundle\Classes\LanguageProcessor\LanguageProcessor;
use ContactBundle\Entity\Contact;
use ContactBundle\Form\ContactType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class MainController extends Controller
{
    /**
     * Get main page items (Articles, sliders, Books, etc)
     *
     * @param Request $request
     * @Route("/", name="front_main_index")
     * @Method({"GET", "POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $newArticles = $em->getRepository('NewsBundle:Article')->findBy(
            array('language' => $this->get('locale.service')->getLanguageByLocale($request->getLocale())));
        $sliders = $em->getRepository('AdBundle:Slider')->findBy(
            array('active' => 1, 'language' => $this->get('locale.service')->getLanguageByLocale($request->getLocale())));
        $lastReleasedBooks = $em->getRepository('AdBundle:Slider')->getFront()->getLastReleasedBooks($request->getLocale());
        $mostViewedBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getMostViewedBooks(
            $this->get('knp_paginator'), $request->query->getInt('page', 1), $request->getLocale());
        $mostDownloadedBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getMostDownloadedBooks(
            $this->get('knp_paginator'), $request->query->getInt('page', 1), $request->getLocale());

        return $this->render('FrontBundle:Main:index.html.twig', [
            'newArticles'           => $newArticles,
            'sliders'               => $sliders,
            'lastReleasedBooks'     => $lastReleasedBooks,
            'mostViewedBooks'       => $mostViewedBooks,
            'mostDownloadedBooks'   => $mostDownloadedBooks
        ]);

    }

    /**
     * Get all active books
     *
     * @param Request $request
     * @Route("/all-books", name="front_main_all_books")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allBooksAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $allBooks = $em->getRepository('SmartlibBundle:Book')->getFront()
            ->getAllBooks(
                $this->get('knp_paginator'),
                $request->query->getInt('page', 1),
                $request->getLocale());

        return $this->render('FrontBundle:Main:all_books.html.twig', [
            'allBooks' => $allBooks
        ]);
    }

    /**
     * Get all categories
     *
     * @param Request $request
     * @Route("/categories", name="front_main_categories")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allCategoriesAction(Request $request)
    {
        $user = $request->getUser();

        return $this->render('FrontBundle:Main/include:categories.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Get user favorite books
     *
     * @param Request $request
     * @Route("/favorite-books", name="front_main_favorite_books")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function favoriteBooksAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $myFavoriteBooks = $em->getRepository('SmartlibBundle:Book')->getFront()
            ->getFavoriteBooks(
                $this->get('knp_paginator'),
                $request->query->getInt('page', 1),
                $request->getLocale()
            );

        return $this->render('FrontBundle:Main:favorite_books.html.twig', [
                'myFavoriteBooks' => $myFavoriteBooks
            ]);
    }

    /**
     * Get list of authors
     *
     * @param Request $request
     * @Route("/authors", name="front_main_authors", defaults={"sortBy"=null})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authorsAction(Request $request, $sortBy)
    {
        $em = $this->getDoctrine()->getManager();
        $authors = $em->getRepository('SmartlibBundle:Person')->getFront()
            ->getAllAuthors(
                $request->getLocale(),
                $this->get('knp_paginator'),
                $request->query->getInt('page', 1),
                $sortBy
            );

        if($this->getUser()){
            $userId = $this->getUser()->getId();
        } else {
            $userId = null;
        }

        $authorsFollowed = $em->getRepository('SmartlibBundle:Person')->getFront()
            ->getFollowedAuthors(
                $request->getLocale(),
                $this->get('knp_paginator'),
                $request->query->getInt('page', 1),
                $sortBy,
                $userId
            );

        return $this->render('FrontBundle:Main:authors.html.twig', [
            'authors'           => $authors,
            'authorsFollowed'   => $authorsFollowed
        ]);
    }

    /**
     * Get author details
     *
     * @param $id
     * @param $request
     * @Route("/author-details/{id}", name="front_main_author_details")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authorDetails(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $authorDetails = $em->getRepository('SmartlibBundle:Person')->getFront()->getAuthorDetails($id, $request->getLocale());
        $authorBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getAuthorBooks($authorDetails);
        $translatorBooksCount = $em->getRepository('SmartlibBundle:Book')->getFront()->getTranslatorBooksCount($authorDetails);
        $translatorBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getTranslatorBooks($authorDetails);

        return $this->render('FrontBundle:Main:author_details.html.twig', [
            'authorDetails'         => $authorDetails,
            'authorBooks'           => $authorBooks,
            'translatorBooks'       => $translatorBooks,
            'translatorBooksCount'  => $translatorBooksCount
        ]);
    }

    /**
     * Get list of translators
     *
     * @param Request $request
     * @Route("/translators", name="front_main_translators")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function translatorsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translators = $em->getRepository('SmartlibBundle:Person')->getFront()
            ->getAllTranslators(
                $request->getLocale(),
                $this->get('knp_paginator'),
                $request->query->getInt('page', 1)
            );

        return $this->render('FrontBundle:Main:translators.html.twig', [
            'translators' => $translators
        ]);
    }

    /**
     * Get translator details
     *
     * @param $id
     * @param $request
     * @Route("/translator-details/{id}", name="front_main_translator_details")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function translatorDetails(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $authorDetails = $em->getRepository('SmartlibBundle:Person')->getFront()->getTranslatorDetails($id, $request->getLocale());
        $authorBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getAuthorBooks($authorDetails);
        $translatorBooksCount = $em->getRepository('SmartlibBundle:Book')->getFront()->getTranslatorBooksCount($authorDetails);
        $translatorBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getTranslatorBooks($authorDetails);

        return $this->render('FrontBundle:Main:translator_details.html.twig', [
            'translatorDetails'     => $authorDetails,
            'authorBooks'           => $authorBooks,
            'translatorBooks'       => $translatorBooks,
            'translatorBooksCount'  => $translatorBooksCount
        ]);
    }

    /**
     * Update books
     *
     * @param Request $request
     * @Route("/updated-books", name="front_main_updated_books")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updatedBooksAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $allBooks = $em->getRepository('SmartlibBundle:Book')->getFront()
            ->getUpdatedBooks(
                $this->get('knp_paginator'),
                $request->query->getInt('page', 1),
                $request->getLocale());

        return $this->render('FrontBundle:Main:updated_books.html.twig', [
            'allBooks' => $allBooks
        ]);
    }

    /**
     * Create new article post
     *
     * @param Request $request
     * @Route("/new-articles", name="front_main_new_articles")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newArticlesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $newArticles = $em->getRepository('NewsBundle:Article')->getFront()->getAllNews(
            $this->get('knp_paginator'),
            $request->query->getInt('page', 1),
            $this->get('locale.service')->getLanguageByLocale($request->getLocale())
        );

        return $this->render('FrontBundle:Main:new_articles.html.twig', [
            'newArticles' => $newArticles
        ]);
    }

    /**
     * Get article details
     *
     * @param $id
     * @param $request
     * @Route("/article-details/{id}", name="front_main_article_details")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function articleDetailsAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $articleDetails = $em->getRepository('NewsBundle:Article')->getFront()->getArticleDetails($id);
        $newArticles = $em->getRepository('NewsBundle:Article')
            ->findBy(array('language' => $this->get('locale.service')->getLanguageByLocale($request->getLocale())));

        return $this->render('FrontBundle:Main:article_details.html.twig', [
            'articleDetails' => $articleDetails,
            'newArticles'    => $newArticles
        ]);
    }

    /**
     * Get mobiles apps
     *
     * @Route("/our-apps", name="front_main_our_apps")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ourAppsAction()
    {
        return $this->render('FrontBundle:Main:our_apps.html.twig');
    }

    /**
     * FAQ
     *
     * @Route("/faq", name="front_main_faq")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function faqAction()
    {
        return $this->render('FrontBundle:Main:faq.html.twig');
    }

    /**
     * Contact us
     *
     * @param Request $request
     * @Route("/contact-us", name="front_main_contact_us")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contactUsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = new Contact();
        $countries = $this->get('locale.service')->getCountries($request->getLocale());
        $form = $this->createForm(ContactType::class, $entity, ['countries' => $countries]);
        $form->handleRequest($request);

        $languageRepository = $em->getRepository('LocaleBundle:Language');
        $languageId = $languageRepository->getLanguageIdByLocale($request->getLocale());

        if($form->isValid() && $form->isSubmitted()){
            $entity->setLanguageId($languageId);
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success','Your message sent'
            );

            return $this->redirectToRoute('front_main_contact_us');

        }

        return $this->render('FrontBundle:Main:contact-us.html.twig', [
            'form'  => $form->createView()
        ]);

    }

    /**
     * Search results
     *
     * @param Request $request
     * @Rest\Route("/search-result", name="front_main_search_result")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchResultAction(Request $request)
    {

        if('POST' == $request->getMethod()) {
            $search = $request->get('search');

            return $this->redirectToRoute('front_main_search_result', [
                'search' => $search
            ]);
        }

        $search = $request->query->get('search');

        $searchService = $this->get('search.service');
        $query = $searchService->getSelect();
        $dismax = $query->getDisMax();
        $dismax->setQueryFields('title, slug, summary');
        $helper = $query->getHelper();
        $searchService->setCurrentPage($request->query->getInt('page', 1));

        if($search){
//            $languageProcessor = new LanguageProcessor($this->get('locale.service')->getLanguageByLocale($request->getLocale()));
//            $searchNormalized = $languageProcessor->processText($search);
            $searchNormalized = $search;
            $query->setQuery($helper->escapeTerm($searchNormalized));
            $searchService->setQuery($helper->escapeTerm($searchNormalized));
        }

        $highlighting = $query->getHighlighting();
        $highlighting->setFields('title, summary');
        $highlighting->setSimplePrefix('<span style="color:red;">');
        $highlighting->setSimplePostfix('</span>');

        $result = $searchService->getResults();


        ////
        $personsSearchService = $this->get('search.service');
        $personsSearchService->setClient($this->container->get('solarium.client.smartlib_person'));
        $personsQuery = $personsSearchService->getSelect();
        $persons_dismax = $personsQuery->getDisMax();
        $persons_dismax->setQueryFields('name_'.$request->getLocale());
        $personsHelper = $personsQuery->getHelper();
        if($search){
            $personsQuery->setQuery($personsHelper->escapeTerm($search));
            $personsSearchService->setQuery($personsHelper->escapeTerm($search));
        }
        $persons = $personsSearchService->getResults();

        return $this->render('FrontBundle:Main:search_result.html.twig', [
            'search'        => $search,
            'result'        => $result,
            'pagination'    => $searchService->getPagination(),
            'persons'       => $persons,
        ]);
    }
}
