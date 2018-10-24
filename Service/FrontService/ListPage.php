<?php

namespace FrontBundle\Service\FrontService;

use FatawaBundle\Classes\LanguageProcessor\LanguageProcessor;

/**
 * Class ListPage
 * @package FrontBundle\Service\FrontService
 */
class ListPage extends AbstractPageService
{


    /**
     * generate list fatawa by category route
     * @param $id
     * @return mixed
     */
    public function getListCategoryFatawaUrl($id)
    {
        $temp = $this->parameters;
        $this->parameters = [];
        $this->setParameter('categories', [$id]);
        $encodedParameters = $this->getParameters(true);
        $this->parameters = $temp;
        return $this->container->get('router')->generate('front_fatawa_list', ['q' => $encodedParameters, 'c' => $id]);
    }

    /**
     * generate list fatawa by source route
     * @param $id
     * @return mixed
     */
    public function getListSourceFatawaUrl($id)
    {
        $temp = $this->parameters;
        $this->parameters = [];
        $this->setParameter('sources', [$id]);
        $encodedParameters = $this->getParameters(true);
        $this->parameters = $temp;
        return $this->container->get('router')->generate('front_fatawa_list', ['q' => $encodedParameters, 's' => $id]);
    }

    /**
     * generate list fatawa by type route
     * @param $id
     * @return mixed
     */
    public function getListFatawaTypeUrl($id)
    {
        $temp = $this->parameters;
        $this->parameters = [];
        $this->setParameter('fatwaTypes', [$id]);
        $encodedParameters = $this->getParameters(true);
        $this->parameters = $temp;
        return $this->container->get('router')->generate('front_fatawa_list', [ 'fatwaTypes' => $id]);
    }

    /**
     * get search results
     * @param int $page
     * @return mixed
     */
    public function search($page = 1)
    {
        $parameters = $this->getParameters();
        $searchService = $this->container->get('search.service');
        $searchService->clearAll();
        $searchService->setCurrentPage($page);
        $search = $this->getSearchParameter($parameters, 'search');
        if($search) {
            $languageProcessor = new LanguageProcessor();
            $search = $languageProcessor->processText($search);
            $helper = $searchService->getSelect()->getHelper();
            $searchService->setQuerydefaultfield('text_s');
            $searchService->setQuery("\"".$helper->escapeTerm($search)."\"");
        }

        $categoryIds = (array) $this->getParameter('categories');
        if(sizeof($categoryIds)) {
            $categoryIds = $this->getCategoryIdsWithChildren($categoryIds);
            $this->addFilterFromArray($categoryIds, 'category_id', $searchService);
        }

        $moftyIds = (array) $this->getParameter('mofties');
        if(sizeof($moftyIds)) {
            $this->addFilterFromArray($moftyIds, 'mofty_id', $searchService);
        }

        $fatwaTypeIds = (array) $this->getParameter('fatwaTypes');
        if(sizeof($fatwaTypeIds)) {
            $this->addFilterFromArray($fatwaTypeIds, 'fatwa_type_id', $searchService);
        }

        $categoryIds = (array) $this->getParameter('categories');
        $categoryIds = $this->clearCategoryIds($categoryIds);
        //print_r($categoryIds);exit;
        if(sizeof($categoryIds)) {
            $this->addFilterFromArray($categoryIds, 'category_id', $searchService);
        }

        $countryIds = $this->getCorrectCountriesParameter();
        $sourceIds = (array) $this->getParameter('sources');
        if(sizeof($sourceIds) || sizeof($countryIds)) {
            $countryIds = array_map(function($e) {
                if(preg_match ( '/^[0-9]*$/', $e) === 1) {
                    return 'country_id:' . trim($e);
                }
            }, $countryIds);

            $sourceIds = array_map(function($e) {
                if(preg_match ( '/^[0-9]*$/', $e) === 1) {
                    return 'source_id:' . trim($e);
                }
            }, $sourceIds);
            $ids = array_merge($sourceIds, $countryIds);
            $fullWhere = implode(' or ', $ids);
            $ids = array_diff( $ids, [null] );
            if(sizeof($ids)) {
                $fullWhere = implode(' or ', $ids);
                $searchService->addFilterQuery('countries_sources', sprintf('%s', $fullWhere));
            }
        }
        $searchService->addFilterQuery('should_be_active', 'should_be_active:true');

        $sort = $this->getParameter('sort');
        if($sort) {
            $dir = $this->getParameter('dir');
            $searchService->addSort($sort, $dir);
        }

        return $searchService;
    }

    /**
     * @param $categoryIds
     * @return array
     */
    private function clearCategoryIds($categoryIds)
    {
        $em = $this->container->get('doctrine')->getManager();
        $out = [];
        foreach($categoryIds as $id) {
            $dql = "SELECT COUNT(c.id)
                    FROM FatawaBundle:Category c
                    WHERE c.parent = :parent 
                    AND c.id IN(:ids) ";
            $query = $em->createQuery($dql);
            $query->setParameters([
                'parent' => $id,
                'ids' => $categoryIds,
            ]);
            if(!$query->getSingleScalarResult()) {
                $out[] = $id;
            }
        }
        $out2 = [];
        foreach($out as $id) {
            $dql = "SELECT d
                    FROM FatawaBundle:CategoryDescendant d
                    WHERE d.node = :node ";
            $query = $em->createQuery($dql);
            $query->setParameters([
                'node' => $id
            ]);
            $result = $query->getResult();
            foreach($result as $row) {
                $out2[] = $row->getDescendant()->getId();
            }
        }
        return array_unique($out2);
    }

    /**
     * @param $type
     * @return bool
     */
    public function isFatwaTypeSelected($type)
    {
        $return = false;
        $fatwaTypes = $this->getParameter('fatwaTypes');
        if($fatwaTypes) {
            if(in_array($type, $fatwaTypes)) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * generate Category Tree in html to use in twig files
     * @param null $parentId
     * @param string $checkboxId
     * @return string
     */
    public function getSearchBoxCategoryTreeHtml($parentId = null, $checkboxId = 'sf')
    {
        $em = $this->container->get('doctrine')->getManager();
        $params = [];
        $dql = "SELECT m
        FROM FatawaBundle:Category m
        WHERE m.deletedAt is NULL ";
        if(is_null($parentId)) {
            $dql .= "AND m.parent IS NULL ";
        }else{
            $dql .= "AND m.parent = :parent ";
            $params['parent'] = $parentId;
        }
        $dql .= "ORDER BY m.sortOrder ";
        $query = $em->createQuery($dql);
        if(count($params)) {
            $query->setParameters($params);
        }
        $result = $query->getResult();
        $html = '<ul>';
        $selectedCategories = (array) $this->getParameter('categories');
        foreach($result as $category) {
            $selected = in_array($category->getId(), $selectedCategories) ? true : false;
            $html .= '<li>';
            $html .= '<div class="tree-item">'.
                '<a><i class="ti-plus"></i></a>'.
                '<div class="checkbox">'.
                '<input type="checkbox" name="categories['.$category->getId().']" id="'.$checkboxId.'-category-'.$category->getId().'" class="css-checkbox" '.($selected ? 'checked="checked"' : '').'/>'.
                '<label for="'.$checkboxId.'-category-'.$category->getId().'" class="css-label">'.$category->getName().'</label>'.
                '</div>'.
                '</div>';
            $html .= $this->getSearchBoxCategoryTreeHtml($category->getId(), $checkboxId );
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * get array of mofties
     * @return array
     */
    public function getMofties()
    {
        $em = $this->container->get('doctrine')->getManager();
        $dql = "SELECT m
                FROM FatawaBundle:ProperName m
                INNER JOIN m.fatwa f
                ORDER BY m.name ";
        $query = $em->createQuery($dql);
        $mofties = $query->getResult();
        $out = [];
        foreach($mofties as $mofty) {
            $moftyIds = (array) $this->getParameter('mofties');
            $out[] = [
                'mofty' => $mofty,
                'selected' => in_array($mofty->getId(), $moftyIds),
            ];
        }
        return $out;
    }

    /**
     * get array of Categories Ids With Children
     * @param $categories
     * @return array
     */
    private function getCategoryIdsWithChildren($categories)
    {
        $em = $this->container->get('doctrine')->getManager();
        $dql = "SELECT cd
        FROM FatawaBundle:CategoryDescendant cd
        WHERE cd.node IN(:nodes) ";

        $query = $em->createQuery( $dql );
        $query->setParameter('nodes', $categories);
        $out = [];
        $categories = $query->getResult();
        foreach($categories as $category) {
            $out[] = $category->getDescendant()->getId();
        }
        return array_unique($out);
    }

}