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
class PersonApiController extends Controller
{
    /**
     * @Route("/persons/{email}")
     * @Method("POST")
     *
     * @param Request $request
     * @param $email
     * @return View
     */
    public function createPersonAction(Request $request, $email)
    {
        $requestContent = $request->getContent();
        $requestContentArr = json_decode($requestContent, true);
//        var_dump($requestContentArr);die;

        $view = View::create();
        $view->setFormat('json');

        if (!$requestContentArr || !array_key_exists('name', $requestContentArr) || !array_key_exists('phobias', $requestContentArr)) {
            $view->setStatusCode(400);
            $view->setData([
                'message' => 'Bad request: JSON data is malformed.',
                'status' => '400',
            ]);

            return $view;
        }

        $userManager = $this->get('wade_core.manager.user_manager');
        $createResult = $userManager->createPerson($email, $requestContentArr);
        if ($createResult === 'true') {
            $data = [
                'name' => $requestContentArr['name'],
                'phobias' => $requestContentArr['phobias'],
            ];

            $view->setStatusCode(201); // 201 Created
            $view->setData([
                'create_result' => $createResult,
                'message' => 'Person created successfully.',
                'data' => $data,
                'status' => '201',
            ]);
        } else {
            $view->setStatusCode(500);
            $view->setData([
                'create_result' => $createResult,
                'message' => 'Server error: Person not created.',
                'status' => '500',
            ]);
        }

        return $view;
    }

    /**
     * @Route("/persons/{email}")
     * @Method("GET")
     *
     * @param $email
     * @return View
     */
    public function getPersonAction($email)
    {
        $view = View::create();
        $view->setFormat('json');

        $userManager = $this->get('wade_core.manager.user_manager');
        $createResult = $userManager->getPersonsForUser($email);

//        if (is_array($createResult) && count($createResult) > 0) {
        if (is_array($createResult)) {
            $view->setStatusCode(200); // 200 OK
            $view->setData([
                'data' => $createResult,
                'status' => '200',
            ]);
        } else {
            $view->setStatusCode(404); // 404 Not Found
            $view->setData([
                'status' => '404',
            ]);
        }

        return $view;
    }
}
