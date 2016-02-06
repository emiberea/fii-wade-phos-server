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
            $view->setData(['message' => 'Bad request: JSON data is malformed.']);

            return $view;
        }

        $loginResult = $this->get('wade_core.manager.user_manager')->authenticateUser($requestContentArr['email'], $requestContentArr['password']);
        if ($loginResult) {
            $view->setStatusCode(200);
            $view->setData([
                'login_result' => $loginResult,
                'message' => 'Authentication success.',
            ]);
        } else {
            $view->setStatusCode(401);
            $view->setData([
                'login_result' => $loginResult,
                'message' => 'Authentication failed.',
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

        if (!$requestContentArr || !array_key_exists('email', $requestContentArr) || !array_key_exists('password', $requestContentArr) || !array_key_exists('name', $requestContentArr)) {
            $view->setStatusCode(400);
            $view->setData(['message' => 'Bad request: JSON data is malformed.']);

            return $view;
        }

        $userManager = $this->get('wade_core.manager.user_manager');
        $user = $userManager->findUserByEmail($requestContentArr['email']);
        if (is_array($user) && ($user['status'] === 'success' || $user['status'] === 'user_multiple')) {
            $view->setStatusCode(409); // 409 Conflict
            $view->setData(['message' => 'Conflict: There is already 1 user with the same email. Internal status: ' . $user['status']]);

            return $view;
        }

        $createResult = $userManager->createUser($requestContentArr);
        if ($createResult === 'true') {
            $view->setStatusCode(201);
            $view->setData([
                'create_result' => $createResult,
                'message' => 'User created successfully.',
            ]);
        } else {
            $view->setStatusCode(500);
            $view->setData([
                'create_result' => $createResult,
                'message' => 'Server error: User not created.',
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
                $view->setStatusCode(200); // 200 OK
                $view->setData($user['data']);

                return $view;
            } elseif ($user['status'] === 'user_not_found') {
                $view->setStatusCode(404); // 404 Not Found

                return $view;
            }
        }

        $view->setStatusCode(500); // 500 Internal Server Error

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
            $view->setData(['message' => 'Bad request: JSON data is malformed.']);

            return $view;
        }

        $userManager = $this->get('wade_core.manager.user_manager');
        $user = $userManager->findUserByEmail($email);
        if (is_array($user)) {
            if ($user['status'] === 'success') {
                $oldUserData = $user['data'];
                $userManager->updateUser($email, $oldUserData, $requestContentArr);

                $view->setStatusCode(204); // 204 No Content (or 200 OK)

                return $view;
            } elseif ($user['status'] === 'user_not_found') {
                $view->setStatusCode(404); // 404 Not Found

                return $view;
            }
        }

        $view->setStatusCode(500); // 500 Internal Server Error

        return $view;
    }
}
