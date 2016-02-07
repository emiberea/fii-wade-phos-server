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
class ApiController extends Controller
{
    /**
     * @Route("/login")
     * @Method("POST")
     *
     * @param Request $request
     * @return View
     */
    public function loginAction(Request $request)
    {
        $requestContent = $request->getContent();
        $requestContentArr = json_decode($requestContent, true);
//        var_dump($requestContentArr);die;

        $view = View::create();
        $view->setFormat('json');

        if (!$requestContentArr || !array_key_exists('email', $requestContentArr) || !array_key_exists('password', $requestContentArr)) {
            $view->setStatusCode(400);
            $view->setData([
                'message' => 'Bad request: JSON data is malformed.',
                'status' => '400',
            ]);

            return $view;
        }

        $loginResult = $this->get('wade_core.manager.user_manager')->authenticateUser($requestContentArr['email'], $requestContentArr['password']);
        if ($loginResult) {
            $view->setStatusCode(200);
            $view->setData([
                'login_result' => $loginResult,
                'message' => 'Authentication success.',
                'status' => '200',
            ]);
        } else {
            $view->setStatusCode(401);
            $view->setData([
                'login_result' => $loginResult,
                'message' => 'Authentication failed.',
                'status' => '401',
            ]);
        }

        return $view;
    }

    /**
     * @Route("/users")
     * @Method("POST")
     *
     * @param Request $request
     * @return View
     */
    public function createUserAction(Request $request)
    {
        $requestContent = $request->getContent();
        $requestContentArr = json_decode($requestContent, true);
//        var_dump($requestContentArr);die;

        $view = View::create();
        $view->setFormat('json');

        if (!$requestContentArr || !array_key_exists('email', $requestContentArr) || !array_key_exists('password', $requestContentArr)
            || !array_key_exists('name', $requestContentArr) || !array_key_exists('phobias', $requestContentArr)
        ) {
            $view->setStatusCode(400);
            $view->setData([
                'message' => 'Bad request: JSON data is malformed.',
                'status' => '400',
            ]);

            return $view;
        }

        $userManager = $this->get('wade_core.manager.user_manager');
        $user = $userManager->findUserByEmail($requestContentArr['email']);
        if (is_array($user) && ($user['status'] === 'success' || $user['status'] === 'user_multiple')) {
            $view->setStatusCode(409); // 409 Conflict
            $view->setData([
                'message' => 'Conflict: There is already 1 user with the same email. Internal status: ' . $user['status'],
                'status' => '409',
            ]);

            return $view;
        }

        $createResult = $userManager->createUser($requestContentArr);
        if ($createResult === 'true') {
            $view->setStatusCode(201);
            $view->setData([
                'create_result' => $createResult,
                'message' => 'User created successfully.',
                'status' => '201',
            ]);
        } else {
            $view->setStatusCode(500);
            $view->setData([
                'create_result' => $createResult,
                'message' => 'Server error: User not created.',
                'status' => '500',
            ]);
        }

        return $view;
    }

    /**
     * @Route("/users/{email}")
     * @Method("GET")
     *
     * @param $email
     * @return View
     */
    public function getUserAction($email)
    {
        $view = View::create();
        $view->setFormat('json');

        $userManager = $this->get('wade_core.manager.user_manager');
        $user = $userManager->findUserByEmail($email);
        if (is_array($user)) {
            if ($user['status'] === 'success') {
                $user['data']['phobias'] = $userManager->findPhobiasForUser($email);

                $view->setStatusCode(200); // 200 OK
                $view->setData([
                    'data' => $user['data'],
                    'status' => '200',
                ]);

                return $view;
            } elseif ($user['status'] === 'user_not_found') {
                $view->setStatusCode(404); // 404 Not Found
                $view->setData([
                    'status' => '404',
                ]);

                return $view;
            }
        }

        $view->setStatusCode(500); // 500 Internal Server Error
        $view->setData([
            'status' => '500',
        ]);

        return $view;
    }

    /**
     * @Route("/users/{email}")
     * @Method("PUT")
     *
     * @param Request $request
     * @param $email
     * @return View
     */
    public function updateUserAction(Request $request, $email)
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
        $user = $userManager->findUserByEmail($email);
        if (is_array($user)) {
            if ($user['status'] === 'success') {
                $oldUserData = $user['data'];
                $userManager->updateUser($email, $oldUserData, $requestContentArr);

                $view->setStatusCode(200); // 200 OK (or 204 No Content)
                $view->setData([
                    'status' => '200',
                ]);

                return $view;
            } elseif ($user['status'] === 'user_not_found') {
                $view->setStatusCode(404); // 404 Not Found
                $view->setData([
                    'status' => '404',
                ]);

                return $view;
            }
        }

        $view->setStatusCode(500); // 500 Internal Server Error
        $view->setData([
            'status' => '500',
        ]);

        return $view;
    }

    /**
     * @Route("/phobias")
     * @Method("GET")
     *
     * @return View
     */
    public function getPhobiaAction()
    {
        $view = View::create();
        $view->setFormat('json');

        $phobiaManager = $this->get('wade_core.manager.phobia_manager');
        $phobias = $phobiaManager->findAllPhobias();

        if (is_array($phobias) && count($phobias) > 0) {
            $phobiasJsonStr = json_encode($phobias);
            $view->setStatusCode(200); // 200 OK
            $view->setData([
                'data' => $phobiasJsonStr,
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
