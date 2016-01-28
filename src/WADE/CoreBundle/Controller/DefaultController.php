<?php

namespace WADE\CoreBundle\Controller;

use EasyRdf_Sparql_Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
//        EasyRdf_Namespace::set('dc', 'http://purl.org/dc/elements/1.1/');
//        EasyRdf_Namespace::set('foaf', 'http://xmlns.com/foaf/0.1/');
//        EasyRdf_Namespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
//        EasyRdf_Namespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
//        EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
//        EasyRdf_Namespace::set('dbp', 'http://dbpedia.org/property/');
//        EasyRdf_Namespace::set('dbr', 'http://dbpedia.org/resource/');

        $sparql = new EasyRdf_Sparql_Client('http://dbpedia.org/sparql');
        $result = $sparql->query(
            'select ?label ?info ?link where{
            ?phobia dct:subject dbc:Phobias .
            ?phobia rdfs:label ?label .
            ?phobia dbo:abstract ?info .
            ?phobia foaf:isPrimaryTopicOf ?link
            filter(lang (?label)="en" and lang(?info)="en")
            }'
        );
        var_dump($result);die;
        return $this->render('WADECoreBundle:Default:index.html.twig');
    }
}
