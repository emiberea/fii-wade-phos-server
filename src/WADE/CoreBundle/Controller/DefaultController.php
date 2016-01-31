<?php

namespace WADE\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use EasyRdf_Serialiser_Arc;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $value = $this->get('wade_core.manager.user_manager')->insertUser('');

        return $this->render('WADECoreBundle:Default:index.html.twig', array(
            'value' => $value,
        ));
    }

    /**
     * @Route("/import-phobia")
     */
    public function importPhobiaAction()
    {
        /** @var \EasyRdf_Sparql_Result $phobiaArr */
        $phobiaArr = $this->get('wade_core.service.dbpedia')->queryPhobias();
        $stardogService = $this->get('wade_core.service.stardog');

        foreach ($phobiaArr as $phobia) {
            $id = $phobia->id->getValue();
            $label = $phobia->label->getValue();
            $info = $phobia->info->getValue();
            $link = $phobia->link->getUri();

            $stardogService->updateDatabase($id, $label, $info, $link);
        }

        return $this->render('WADECoreBundle:Default:importPhobia.html.twig');
    }

    /**
     * @Route("/create-person")
     */
    public function createPersonAction()
    {
        \EasyRdf_Format::registerSerialiser('ntriples', 'EasyRdf_Serialiser_Arc');
        \EasyRdf_Format::registerSerialiser('posh', 'EasyRdf_Serialiser_Arc');
        \EasyRdf_Format::registerSerialiser('rdfxml', 'EasyRdf_Serialiser_Arc');
        \EasyRdf_Format::registerSerialiser('turtle', 'EasyRdf_Serialiser_Arc');

        \EasyRdf_Namespace::set('foaf', 'http://xmlns.com/foaf/0.1/');

        $uri = 'http://www.example.com/emi#me';
        $name = 'Emi Berea';
        $emailStr = 'emi.berea@gmail.com';
        $homepageStr = 'http://bereae.me/';

        $graph = new \EasyRdf_Graph();
        # 1st Technique
        $me = $graph->resource($uri, 'foaf:Person');
        $me->set('foaf:name', $name);
        if ($emailStr) {
            $email = $graph->resource("mailto:".$emailStr);
            $me->add('foaf:mbox', $email);
        }
        if ($homepageStr) {
            $homepage = $graph->resource($homepageStr);
            $me->add('foaf:homepage', $homepage);
        }

        # Finally output the graph
        $data = $graph->serialise('rdfxml');
        if (!is_scalar($data)) {
            $data = var_export($data, true);
        }
        var_dump($data);die;
    }
}
