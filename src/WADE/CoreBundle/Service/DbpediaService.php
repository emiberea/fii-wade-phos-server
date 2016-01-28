<?php

namespace WADE\CoreBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class DbpediaService
{
    /** @var ContainerInterface $container */
    private $container;

    /** @var string $endpoint */
    private $endpoint;

    /** @var \EasyRdf_Sparql_Client $sparqlClient */
    private $sparqlClient;

    /**
     * @param ContainerInterface $container
     * @param $endpoint
     */
    public function __construct(ContainerInterface $container, $endpoint)
    {
        $this->container = $container;
        $this->endpoint = $endpoint;
        $this->sparqlClient = new \EasyRdf_Sparql_Client($this->endpoint);
    }

    public function queryPhobias()
    {
        $result = $this->sparqlClient->query(
            'select ?label ?info ?link where{
                ?phobia dct:subject dbc:Phobias .
                ?phobia rdfs:label ?label .
                ?phobia dbo:abstract ?info .
                ?phobia foaf:isPrimaryTopicOf ?link
                filter(lang (?label)="en" and lang(?info)="en")
                }'
        );

        // TODO: remove
//        var_dump($result);die;
        return $result;
    }
}
