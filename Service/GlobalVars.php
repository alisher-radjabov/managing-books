<?php

namespace FrontBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class GlobalVars implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Get list of categories service
     *
     * @param $locale
     * @return mixed
     */
    public function getCategories($locale)
    {
        $em = $this->container->get('doctrine')->getManager();
        $category = $em->getRepository('SmartlibBundle:ProjectCategory')->getFront()->getAllCategories($locale);
        return $category;
    }

    /**
     * Get list of parent categories service
     *
     * @param $locale
     * @return mixed
     */
    public function getParentCategories($locale)
    {
        $em = $this->container->get('doctrine')->getManager();
        $category = $em->getRepository('SmartlibBundle:ProjectCategory')->getFront()->getParentCategories($locale);
        return $category;
    }

    /**
     * Get list of child categories
     *
     * @param $id
     * @return mixed
     */
    public function getChildCategories($id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $category = $em->getRepository('SmartlibBundle:ProjectCategory')->getFront()->getChildCategories($id);
        return $category;
    }

    /**
     * Get author details
     *
     * @param $id
     * @param $locale
     * @return mixed
     */
    public function getAuthorDetails($id, $locale)
    {
        $em = $this->container->get('doctrine')->getManager();
        $author = $em->getRepository('SmartlibBundle:Person')->getFront()->getAuthorDetails($id, $locale);
        return $author;
    }

    /**
     * Get list of authors
     *
     * @param $locale
     * @return mixed
     */
    public function getAuthors($locale)
    {
        $em = $this->container->get('doctrine')->getManager();
        $authors = $em->getRepository('SmartlibBundle:Person')->getFront()->getAllAuthors($locale);
        return $authors;
    }

    /**
     * Get count of authors followed
     *
     * @param $user
     * @return mixed
     */
    public function getAuthorsFollowCount($user)
    {
        $em = $this->container->get('doctrine')->getManager();
        $authorsCount = $em->getRepository('SmartlibBundle:Person')->getFront()->getAuthorsFollowCount($user);
        return $authorsCount;
    }

    /**
     * Get authors followed
     *
     * @param $user
     * @return mixed
     */
    public function getFollowAuthors($user){
        $em = $this->container->get('doctrine')->getManager();
        $authorsFollow = $em->getRepository('SmartlibBundle:Person')->getFront()->getFollowAuthors($user);
        return $authorsFollow;

    }

    /**
     * @param $locale
     * @param $author
     * @return mixed
     */
    public function getFollowUserAuthors($locale, $author){
        $em = $this->container->get('doctrine')->getManager();
        $userFollow = $em->getRepository('SmartlibBundle:Person')->getFront()->getFollowUserAuthors($locale, $author);
        return $userFollow;

    }

    /**
     * Get count of translators followed
     *
     * @param $user
     * @return mixed
     */
    public function getTranslatorsFollowCount($user)
    {
        $em = $this->container->get('doctrine')->getManager();
        $authorsCount = $em->getRepository('SmartlibBundle:Person')->getFront()->getTranslatorsFollowCount($user);
        return $authorsCount;

    }

    /**
     * Get list of translators
     *
     * @param $locale
     * @return mixed
     */
    public function getTranslators($locale)
    {
        $em = $this->container->get('doctrine')->getManager();
        $authors = $em->getRepository('SmartlibBundle:Person')->getFront()->getAllTranslators($locale);
        return $authors;
    }

    /**
     * Get authors books count
     *
     * @param $author
     * @return mixed
     */
    public function getAuthorBooksCount($author)
    {
        $em = $this->container->get('doctrine')->getManager();
        $authorBooksCount = $em->getRepository('SmartlibBundle:Book')->getFront()->getAuthorBooksCount($author);

        return $authorBooksCount;
    }

    /**
     * Get other parts of a book
     *
     * @param $collection
     * @return mixed
     */
    public function getOtherBookParts($collection)
    {
        $em = $this->container->get('doctrine')->getManager();
        $otherBookParts = $em->getRepository('SmartlibBundle:Book')->getFront()->getOtherBookParts($collection);

        return $otherBookParts;
    }

    /**
     * Get count of other parts of a book
     *
     * @param $collection
     * @return mixed
     */
    public function getOtherBookPartsCount($collection)
    {
        $em = $this->container->get('doctrine')->getManager();
        $otherBookParts = $em->getRepository('SmartlibBundle:Book')->getFront()->getOtherBookPartsCount($collection);

        return $otherBookParts;
    }

    /**
     * Get translator books count
     *
     * @param $translator
     * @return mixed
     */
    public function getTranslatorBooksCount($translator)
    {
        $em = $this->container->get('doctrine')->getManager();
        $translatorBooksCount = $em->getRepository('SmartlibBundle:Book')->getFront()->getTranslatorBooksCount($translator);

        return $translatorBooksCount;
    }

    /**
     * Get translator details
     *
     * @param $id
     * @param $locale
     * @return mixed
     */
    public function getTranslatorDetails($id, $locale)
    {
        $em = $this->container->get('doctrine')->getManager();
        $translator = $em->getRepository('SmartlibBundle:Person')->getFront()->getTranslatorDetails($id, $locale);
        return $translator;
    }

    /**
     * Get count of all books
     *
     * @return mixed
     */
    public function getAllBooksCount()
    {
        $em = $this->container->get('doctrine')->getManager();
        $allBooksCount = $em->getRepository('SmartlibBundle:Book')->getFront()->getAllBooksCount();

        return $allBooksCount;
    }

    /**
     * Get all downloads count
     *
     * @return mixed
     */
    public function getAllDownloadsCount()
    {
        $em = $this->container->get('doctrine')->getManager();
        $allDownloadsCount = $em->getRepository('SmartlibBundle:Book')->getFront()->getAllDownloadsCount();

        return $allDownloadsCount;
    }

    /**
     * Get all views count
     *
     * @return mixed
     */
    public function getAllViewsCount()
    {
        $em = $this->container->get('doctrine')->getManager();
        $allDownloadsCount = $em->getRepository('SmartlibBundle:Book')->getFront()->getAllViewsCount();

        return $allDownloadsCount;
    }

    /**
     * Get random keywords
     *
     * @param $locale
     * @return mixed
     */
    public function getRandomKeywords($locale)
    {
        $em = $this->container->get('doctrine')->getManager();
        $randomKeywords = $em->getRepository('SmartlibBundle:Book')->getFront()->getRandomKeywords($locale);

        return $randomKeywords;
    }



}