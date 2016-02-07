<?php

namespace WADE\CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use WADE\CoreBundle\Service\StardogService;

class RemedyManager
{
    /** @var ContainerInterface $container */
    private $container;

    /** @var StardogService $stardogService */
    private $stardogService;

    /**
     * @param ContainerInterface $container
     * @param StardogService $stardogService
     */
    public function __construct(ContainerInterface $container, StardogService $stardogService)
    {
        $this->container = $container;
        $this->stardogService = $stardogService;
    }

    /**
     * @param $remedyRequestData
     * @return array
     */
    public function findRemedyFromRequest($remedyRequestData)
    {
        $remedyPerPhobiaArr = [];
        foreach ($remedyRequestData as $item) {
            $sparql = '
                PREFIX remedies: <http://phobia.vrinceanu.com/remedies#>

                SELECT ?description
                WHERE {
                    ?r remedies:forPhobia <' . $item['phobia'] . '> .
                    ?r remedies:forActivity "' . $item['activity'] . '" .
                    ?r remedies:description ?description .
                }';

            $sparqlResult = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_QUERY);
            $remedyArr = $this->processRemedyJsonString($sparqlResult);

            $remedyPerPhobiaArr[] = [
                'phobia' => $item['phobia'],
                'description' => $remedyArr,
            ];
        }

        return $remedyPerPhobiaArr;
    }

    /**
     * @param $phobia
     * @return array
     */
    public function findSymptomsForPhobia($phobia)
    {
        $sparql = '
            PREFIX remedies: <http://phobia.vrinceanu.com/remedies#>

            SELECT ?symptomName
            WHERE {
                ?r remedies:appearsFor "' . $phobia . '" .
                ?r remedies:symptomName ?symptomName .
            }';

        $sparqlResult = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_QUERY);
        $symptomArr = $this->processSymptomJsonString($sparqlResult);

        return $symptomArr;
    }

    /**
     * @param $remedyJsonStr
     * @return array
     */
    private function processRemedyJsonString($remedyJsonStr)
    {
        $responseArr = json_decode($remedyJsonStr, true);
        $remedyRawArr = $responseArr['results']['bindings'];

        $remedyArr = [];
        foreach ($remedyRawArr as $remedy) {
            $remedyArr[] = $remedy['description']['value'];
        }

        return $remedyArr;
    }

    /**
     * @param $symptomJsonStr
     * @return array
     */
    private function processSymptomJsonString($symptomJsonStr)
    {
        $responseArr = json_decode($symptomJsonStr, true);
        $symptomRawArr = $responseArr['results']['bindings'];

        $symptomArr = [];
        foreach ($symptomRawArr as $symptom) {
            $symptomArr[] = $symptom['symptomName']['value'];
        }

        return $symptomArr;
    }
}
