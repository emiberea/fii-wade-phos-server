<?php

namespace WADE\CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use WADE\CoreBundle\Service\DbpediaService;
use WADE\CoreBundle\Service\StardogService;

class PhobiaManager
{
    /** @var ContainerInterface $container */
    private $container;

    /** @var DbpediaService $dbpediaService */
    private $dbpediaService;

    /** @var StardogService $stardogService */
    private $stardogService;

    /**
     * @param ContainerInterface $container
     * @param DbpediaService $dbpediaService
     * @param StardogService $stardogService
     */
    public function __construct(ContainerInterface $container, DbpediaService $dbpediaService, StardogService $stardogService)
    {
        $this->container = $container;
        $this->dbpediaService = $dbpediaService;
        $this->stardogService = $stardogService;
    }

    /**
     * @param $id
     * @param $label
     * @param $info
     * @param $link
     * @return mixed
     */
    public function updateDatabase($id, $label, $info, $link)
    {
        $info = str_replace("/", "", $info);
        $info = str_replace("\\", "", $info);
        $info = str_replace("'", "", $info);
        $info = str_replace("\"", "", $info);

        $sparql = '
            PREFIX dbo: <http://dbpedia.org/ontology/>
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>

            INSERT DATA
                { <http://phobia.vrinceanu.com/remedies#' . str_replace(" ", "_", $label) .'>
                           rdfs:label "' . $label .'";
                           dbo:abstract "' . $info . '";
                           foaf:isPrimaryTopicOf "' . $link . '" .
               }';

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_UPDATE);

        return $result;
    }

    public function findAllPhobias()
    {
        $sparqlResult = $this->queryAllPhobias();
        $result = $this->processPhobiaJsonString($sparqlResult);

        return $result;
    }

    public function queryAllPhobias()
    {
        $sparql = '
            PREFIX dbo: <http://dbpedia.org/ontology/>
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>

            SELECT * WHERE  {
                ?phobia rdfs:label ?label .
                ?phobia dbo:abstract ?info .
                ?phobia foaf:isPrimaryTopicOf ?link .
            }';

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_QUERY);

        return $result;
    }

    /**
     * @param $phobiaJsonStr
     * @return array
     */
    private function processPhobiaJsonString($phobiaJsonStr)
    {
        $responseArr = json_decode($phobiaJsonStr, true);
        $phobiaRawArr = $responseArr['results']['bindings'];

        $phobiaArr = [];
        foreach ($phobiaRawArr as $phobia) {
            $phobiaArr[] = [
                'phobia' => $phobia['phobia']['value'],
                'label' => $phobia['label']['value'],
                'info' => $phobia['info']['value'],
                'link' => $phobia['link']['value'],
            ];
        }

        return $phobiaArr;
    }
}
