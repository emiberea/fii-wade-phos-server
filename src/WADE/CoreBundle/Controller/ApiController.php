<?php

namespace WADE\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/login")
     * @Method("POST")
     *
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $requestContent = $request->getContent();
        $requestContentArr = json_decode($requestContent, true);

//        var_dump($requestContentArr);die;

        return new JsonResponse([]);
    }

    /**
     * @Route("/user")
     * @Method("POST")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addUserAction(Request $request)
    {
        $requestContent = $request->getContent();
        $requestContentArr = json_decode($requestContent, true);
//        var_dump($requestContentArr);die;

        $insertResult = $this->get('wade_core.manager.user_manager')->insertUser($requestContentArr);
        if ($insertResult === 'true') {
            return new JsonResponse(array(
                'status' => 'ok',
                'response' => $insertResult,
            ));
        } else {
            return new JsonResponse(array(
                'status' => 'error',
                'response' => $insertResult,
            ), 400);
        }
    }
}
