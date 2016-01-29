<?php

namespace WADE\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {

        $x = $this->get('wade_core.service.stardog')->updateDatabase('');
        return $this->render('WADECoreBundle:Default:index.html.twig', array(
            'json' => $x,
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
}
