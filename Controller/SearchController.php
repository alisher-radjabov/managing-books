<?php

namespace FrontBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/search")
 */
class SearchController extends Controller
{

    /**
     * Get search results
     *
     * @param Request $request
     * @Route("/", name="front_search_index")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        if('POST' == $request->getMethod()) {
            $smartlibService = $this->get('smartlib.service');
            $search = $request->get('search');

            return $this->redirectToRoute('front_search_index', [
                'search' => $search
            ]);
        }

        $search = $request->query->get('search');
        $searchService = $this->get('search.service');

        $query = $searchService->getSelect();
        $dismax = $query->getDisMax();
        $dismax->setQueryFields('title, text_s, text, text_n');

        $helper = $query->getHelper();

        $searchService->setCurrentPage($request->query->getInt('page', 1));

        if ($search) {
            $languageProcessor = new LanguageProcessor('ar');
            $searchNormalized = $languageProcessor->processText($search);
            $query->setQuery($helper->escapeTerm($searchNormalized));
            $searchService->setQuery($helper->escapeTerm($searchNormalized));
        }

        $hl = $query->getHighlighting();
        $hl->setFields('title, question');
        $hl->setSimplePrefix('<span style="color:red;">');
        $hl->setSimplePostfix('</span>');

        $result = $searchService->getResults();

        return $this->render('FrontBundle:Search:index.html.twig', [
            'search'        => $search,
            'result'        => $result,
            'pagination'    => $searchService->getPagination(),
        ]);
    }

    /**
     * Search autocomplete
     *
     * @param Request $request
     * @Route("/auto-complete", name="front_search_auto_complete")
     * @return JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $smartlibService = $this->get('smartlib.service');
        $list = $smartlibService->getAutoComplete($request->query->get('term'));
        $out = [];
        foreach ($list as $word) {
            $out[] = (object)["id" => "", "label" => $word, "value" => ""];
        }
        return new JsonResponse($out);
    }

}