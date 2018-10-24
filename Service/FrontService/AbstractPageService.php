<?php

namespace FrontBundle\Service\FrontService;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class AbstractPageService
 * @package FrontBundle\Service\FrontService
 */
abstract class AbstractPageService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $parameters = [];

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name)
    {
        if(array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }
    }

    /**
     * @param bool $encoded
     * @return array|string
     */
    public function getParameters($encoded = false)
    {
        if($encoded) {
            $serialized = serialize($this->parameters);
            $baseEncoded = base64_encode($serialized);
            return $baseEncoded;
        }else{
            return $this->parameters;
        }

    }

    /**
     * @param $parameters
     * @param bool $encoded
     * @return $this
     */
    public function setParameters($parameters, $encoded = false)
    {
        if($parameters) {
            if($encoded) {
                $baseDecoded = base64_decode($parameters);
                $unserialized = unserialize($baseDecoded);
                $this->parameters = $unserialized;
            }else{
                $this->parameters = $parameters;
            }
        }else{
            $this->parameters = [];
        }
        return $this;
    }

    /**
     * @param $ids
     * @param $paramName
     * @param $searchService
     */
    protected function addFilterFromArray($ids, $paramName, &$searchService)
    {
        $ids = array_map(function($e) use($paramName) {
            if(preg_match ( '/^[0-9]*$/', $e) === 1) {
                return $paramName.':' . trim($e);
            }
        }, $ids);
        $fullWhere = implode(' or ', $ids);
        $ids = array_diff( $ids, [null] );
        if(sizeof($ids)) {
            $fullWhere = implode(' or ', $ids);
            $searchService->addFilterQuery($paramName, sprintf('%s', $fullWhere));
        }
    }

    /**
     * @return array
     */
    public function getArrayOfCountriesAndSources()
    {
        $out = [];
        $em = $this->container->get('doctrine')->getManager();
        $dql = "SELECT c
                FROM AdminBundle:Country c
                INNER JOIN FatawaBundle:Source s WITH c = s.country        
                WHERE s.active = true 
                ORDER BY c.name ";
        $query = $em->createQuery( $dql );
        $countries = $query->getResult();
        foreach($countries as $country) {
            $dql2 = "SELECT s
                    FROM FatawaBundle:source s       
                    WHERE s.country = :country 
                    AND s.active = true
                    ORDER BY s.name ";
            $query2 = $em->createQuery( $dql2 );
            $query2->setParameter('country', $country);
            $entities = $query2->getResult();
            $sources = [];
            foreach ($entities as $source) {
                $sources[] = [
                    'source' => $source,
                    'selected' => $this->isSourceSelected($source->getId())
                ];
            }
            $out[] = [
                'country' => $country,
                'selected' => $this->isCountrySelected($country->getId()),
                'sources' => $sources
            ];
        }
        return $out;
    }

    /**
     * @return array
     */
    public function getArrayOfSources()
    {
        $em = $this->container->get('doctrine')->getManager();
        $dql2 = "SELECT s
                FROM FatawaBundle:source s       
                WHERE s.active = true
                ORDER BY s.name ";
        $query2 = $em->createQuery( $dql2 );
        $entities = $query2->getResult();
        $sources = [];
        foreach ($entities as $source) {
            $sources[] = [
                'source' => $source,
                'selected' => $this->isSourceSelected($source->getId())
            ];
        }

        return $sources;
    }

    /**
     * @return array
     */
    protected function getCorrectCountriesParameter()
    {
        $out = [];
        $countries = (array) $this->getParameter('countries');
        $countriesAndSources = $this->getArrayOfCountriesAndSources();
        foreach ($countries as $countryId) {
            foreach ($countriesAndSources as $countryElement) {
                if($countryId == $countryElement['country']->getId()) {
                    $found = false;
                    foreach($countryElement['sources'] as $source) {
                        if(in_array($source['source']->getId(), (array) $this->getParameter('sources'))) {
                            $found = true;
                            break;
                        }
                    }
                    if(!$found) {
                        $out[] = $countryId;
                    }
                }
            }
        }
        return $out;
    }

    /**
     * @param $parameters
     * @param $name
     * @return mixed
     */
    protected function getSearchParameter($parameters, $name)
    {
        if(is_array($parameters)) {
            if(array_key_exists($name, $parameters)) {
                $value = $parameters[$name];
                if(is_array($value)) {
                    if(sizeof($value)) {
                        return $value;
                    }
                }else{
                    return $value;
                }
            }
        }
    }

    /**
     * @param $sourceId
     * @return bool
     */
    protected function isSourceSelected($sourceId)
    {
        return in_array($sourceId, (array) $this->getParameter('sources'));
    }

    /**
     * @param $countryId
     * @return bool
     */
    protected function isCountrySelected($countryId)
    {
        return in_array($countryId, (array) $this->getParameter('countries'));
    }
}