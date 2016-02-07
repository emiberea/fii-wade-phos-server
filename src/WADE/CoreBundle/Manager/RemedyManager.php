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
}
