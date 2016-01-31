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

    public function updateDatabase($id, $label, $info, $link)
    {
        $sparql = '
            PREFIX dbo: <http://dbpedia.org/ontology/>
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>

            INSERT DATA
                { <http://phobia.vrinceanu.com/#' . $id .'>
                           rdfs:label "' . $label .'";
                           dbo:abstract "' . $info . '";
                           foaf:isPrimaryTopicOf "' . $link . '" .
               }';

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_UPDATE);

        return $result;
    }
}
