<?php

namespace WADE\CoreBundle\Controller;

use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api")
 */
class RemedyApiController extends Controller
{
    /**
     * @Route("/remedies")
     * @Method("POST")
     *
     * @param Request $request
     * @return View
     */
    public function suggestRemediesAction(Request $request)
    {
        $requestContent = $request->getContent();
        $requestContentArr = json_decode($requestContent, true);
//        var_dump($requestContentArr);die;

        $view = View::create();
        $view->setFormat('json');

        if (!is_array($requestContentArr)) {
            $view->setStatusCode(400);
            $view->setData([
                'message' => 'Bad request: JSON data is malformed.',
                'status' => '400',
            ]);

            return $view;
        }

        $remedyManager = $this->get('wade_core.manager.remedy_manager');
        $remedies = $remedyManager->findRemedyFromRequest($requestContentArr);
        if (is_array($remedies)) {
            $view->setStatusCode(200); // 200 OK
            $view->setData([
                'data' => $remedies,
                'status' => '200',
            ]);

            return $view;
        }

        $view->setStatusCode(500); // 500 Error

        return $view;
    }
}
