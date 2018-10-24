<?php

namespace FrontBundle\Service\FrontService;

use FatawaBundle\Entity\Fatwa;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Implements the methods that used in frontend
 * Class FrontService
 * @package FrontBundle\Service\FrontService
 */
class FrontService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $listPage;
    private $comparePage;

    /**
     * @return ListPage
     */
    public function getListPage()
    {
        if (!$this->listPage) {
            $this->listPage = new ListPage();
            $this->listPage->setContainer($this->container);
        }
        return $this->listPage;
    }

    /**
     * @return ComparePage
     */
    public function getComparePage()
    {
        if (!$this->comparePage) {
            $this->comparePage = new ComparePage();
            $this->comparePage->setContainer($this->container);
        }
        return $this->comparePage;
    }

    /**
     * @return ListPage
     */
    public function getListPageFactory()
    {
        $listPage = new ListPage();
        $listPage->setContainer($this->container);
        return $listPage;
    }


    /**
     * @param $user
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getUserFavorites($user)
    {
        $em = $this->container->get('doctrine')->getManager();
        $fatwaRepository = $em->getRepository('FatawaBundle:Fatwa');
        return $fatwaRepository->getUserFavorites($user);
    }

    /**
     * @param $id
     * @return \Doctrine\Common\Persistence\ObjectRepository|null|object
     */
    public function getFatwaById($id)
    {
        $em = $this->container->get('doctrine')->getManager();
        return $em->getRepository('FatawaBundle:Fatwa')->find($id);
    }

    /**
     * @param $id
     * @return \Doctrine\Common\Persistence\ObjectRepository|null|object
     */
    public function getCategoryById($id)
    {
        $em = $this->container->get('doctrine')->getManager();
        return $em->getRepository('FatawaBundle:Category')->find($id);
    }

    /**
     * @param $category
     * @return string
     * @throws \Twig\Error\Error
     */
    public function getCategoryFullPathHtml($category)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Category');
        $path = $repository->getCategoryFullPath($category);
        $engine = $this->container->get('templating');
        return $engine->render('FrontBundle::_fatwaCategoryPath.html.twig', ['path' => $path]);
    }

    /**
     * @param $category
     * @return string
     * @throws \Twig\Error\Error
     */
    public function getSourceCategoryFullPathHtml($category)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Category');
        $path = $repository->getCategoryFullPath($category);
        $engine = $this->container->get('templating');
        return $engine->render('FrontBundle::_fatwaCategoryPath.html.twig', ['path' => $path]);
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Category');
        $tree = $repository->getFront()->getCategoryTree();
        return $tree;
    }

    public function getMostViewsCategories($limit = 6)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Category');
        $tree = $repository->getFront()->getMostViewsCategories($limit);
        return $tree;
    }

    public function increaseFatwaVisits($fatwa)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Fatwa');
        $repository->increaseFatwaVisits(
            $this->container->get('search.service')->getIndexer(),
            $fatwa
        );
    }

    public function generateFatwaDocument($fatwa)
    {
        return $this->container->get('search.service')->getIndexer()->generateFatwaDocument($fatwa);
    }

    public function addToFavorite($user, $fatwa, $url = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Fatwa');
        return $repository->addToFavorite($user, $fatwa, $url);
    }

    public function updateFavorite($user, $fatwa, $favoriteId, $newUrl)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Fatwa');
        return $repository->updateFavorite($user, $fatwa, $favoriteId, $newUrl);
    }

    public function removeFromFavorite($user, $fatwa, $url = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Fatwa');
        return $repository->removeFromFavorite($user, $fatwa, $url);
    }

    public function addComment($user, $fatwa, $comment)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Fatwa');
        return $repository->addComment($user, $fatwa, $comment);
    }

    public function getUserComments($user)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Fatwa');
        return $repository->getUserComments($user);
    }

    public function getFatwaFavorite($user, $fatwa, $url = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Fatwa');
        if (!$fatwa instanceof Fatwa) {
            $fatwa = $this->getFatwaById($fatwa);
        }
        if ($user) {
            return $repository->getFatwaFavoriteEntity($user, $fatwa, $url);
        }
    }

    public function getFatwaFavoriteToEdit($user, $fatwa, $favoriteId)
    {
        $em = $this->container->get('doctrine')->getManager();
        $repository = $em->getRepository('FatawaBundle:Fatwa');
        if (!$fatwa instanceof Fatwa) {
            $fatwa = $this->getFatwaById($fatwa);
        }
        if ($user) {
            return $repository->getFatwaFavoriteEntityToEdit($user, $fatwa, $favoriteId);
        }
    }

    public function getCountryCount()
    {
        $em = $this->container->get('doctrine')->getManager();
        $dql = "SELECT COUNT(c.id)
        FROM AdminBundle:Country c
        WHERE EXISTS (SELECT f.id FROM FatawaBundle:Fatwa f WHERE f.country = c.id AND f.active = true) ";
        $query = $em->createQuery($dql);
        return $query->getSingleScalarResult();
    }

    public function getSourceCount()
    {
        $em = $this->container->get('doctrine')->getManager();
        $dql = "SELECT COUNT(s.id)
        FROM FatawaBundle:Source s
        WHERE EXISTS (SELECT f.id FROM FatawaBundle:Fatwa f WHERE f.source = s.id AND f.active = true) 
        AND s.active = true ";
        $query = $em->createQuery($dql);
        return $query->getSingleScalarResult();
    }

}