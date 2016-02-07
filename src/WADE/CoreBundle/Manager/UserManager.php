<?php

namespace WADE\CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use WADE\CoreBundle\Service\StardogService;

class UserManager
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
     * @param string $email
     * @param string $plainPassword
     * @return bool
     * @throws \Exception
     */
    public function authenticateUser($email, $plainPassword)
    {
        $user = $this->findUserByEmail($email);
        if (is_array($user) && $user['status'] === 'success') {
            $userData = $user['data'];
            if (is_array($userData) && array_key_exists('password', $userData) && $userData['password'] === sha1(trim($plainPassword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $email
     * @return mixed
     * @throws \Exception
     */
    public function findUserByEmail($email)
    {
        $sparql = '
            PREFIX foaf:  <http://xmlns.com/foaf/0.1/>
            SELECT *
            WHERE {
                ?person foaf:mbox ?email .
                ?person foaf:name ?name .
                ?person foaf:sha1 ?password .
                FILTER (?email = "' . $email . '")
            }';

        $sparqlResult = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_QUERY);
        $usersArr = $this->processUserJsonString($sparqlResult);
        if (count($usersArr) > 1) {
            return [
                'status' => 'user_multiple',
                'message' => 'Multiple users with the same email.',
                'data' => null,
            ];
        } elseif (!array_key_exists(0, $usersArr)) {
            return [
                'status' => 'user_not_found',
                'message' => 'User with email ' . $email . ' does not exists.',
                'data' => null,
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Single user found with success.',
            'data' => $usersArr[0],
        ];
    }

    /**
     * @param $id
     * @param string $param
     * @return array
     */
    public function findPhobiasForUser($id, $param = 'foaf:mbox')
    {
        $sparql = '
            PREFIX foaf:  <http://xmlns.com/foaf/0.1/>
            PREFIX remedies: <http://phobia.vrinceanu.com/remedies#>

            SELECT ?phobia
            WHERE {
                ?person ' . $param . ' "' . $id . '" .
                ?person remedies:hasPhobia ?phobia
            }';

        $sparqlResult = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_QUERY);
        $phobiasArr = $this->processUserPhobiasJsonString($sparqlResult);

        return $phobiasArr;
    }

    /**
     * @param $email
     * @param $oldUser
     * @param $newUser
     * @return string
     */
    public function updateUser($email, $oldUser, $newUser)
    {
        // delete
        $deleteSparql = '
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DELETE WHERE
            {
                 <http://phobia.vrinceanu.com/user#' . $email .'>
                       foaf:mbox "' . $email .'";
                       foaf:name "' . $oldUser['name'] . '" .
           }';

        $deleteResult = $this->stardogService->executeStatement($deleteSparql, StardogService::EXECUTE_UPDATE);

        // insert
        $insertSparql = '
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            INSERT DATA
            {
                 <http://phobia.vrinceanu.com/user#' . $email .'>
                       foaf:mbox "' . $email .'";
                       foaf:name "' . $newUser['name'] . '" .
           }';

        $insertResult = $this->stardogService->executeStatement($insertSparql, StardogService::EXECUTE_UPDATE);

        if ($deleteResult === 'true' && $insertResult === 'true') {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function deleteUser($user)
    {
        $sparql = '
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DELETE WHERE
            {
                 <http://phobia.vrinceanu.com/user#' . $user['email'] .'>
                       foaf:mbox "' . $user['email'] .'";
                       foaf:name "' . $user['name'] . '" .

           }';

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_UPDATE);

        return $result;
    }

    /**
     * @param $user
     * @return mixed
     */
    public function createUser($user)
    {
        if (array_key_exists('phobias', $user) && is_array($user['phobias']) && count($user['phobias']) > 0) {
            $sparqlStatementStr = '';
            foreach ($user['phobias'] as $phobia) {
                $sparqlStatementStr = $sparqlStatementStr . "\n" . '<http://phobia.vrinceanu.com/remedies#hasPhobia>"' . $phobia . '";';
            }
            $sparqlStatementStr = rtrim($sparqlStatementStr, ';');

            $sparql = '
                PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                INSERT DATA
                {
                    <http://phobia.vrinceanu.com/user#' . $user['email'] .'>
                        foaf:mbox "' . $user['email'] .'";
                        foaf:name "' . $user['name'] . '";
                        foaf:sha1 "' . sha1($user['password']) . '";' .
                    $sparqlStatementStr .
                '}';
        } else {
            $sparql = '
                PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                INSERT DATA
                {
                     <http://phobia.vrinceanu.com/user#' . $user['email'] .'>
                           foaf:mbox "' . $user['email'] .'";
                           foaf:name "' . $user['name'] . '";
                           foaf:sha1 "' . sha1($user['password']) . '" .

               }';
        }

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_UPDATE);

        return $result;
    }

    /**
     * @param $email
     * @param $user
     * @return mixed
     */
    public function createPerson($email, $user)
    {
        $uniqueID = md5(uniqid() . time() . mt_rand());
        if (array_key_exists('phobias', $user) && is_array($user['phobias']) && count($user['phobias']) > 0) {
            $sparqlStatementStr = '';
            foreach ($user['phobias'] as $phobia) {
                $sparqlStatementStr = $sparqlStatementStr . "\n" . '<http://phobia.vrinceanu.com/remedies#hasPhobia>"' . $phobia . '";';
            }
            $sparqlStatementStr = rtrim($sparqlStatementStr, ';');

            $sparql = '
                PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                INSERT DATA
                {
                     <http://phobia.vrinceanu.com/user#' . $uniqueID .'>
                           foaf:knows <http://phobia.vrinceanu.com/user#' . $email .'>;
                           foaf:nickname "' . $uniqueID . '";
                           foaf:name "' . $user['name'] . '";'
                           . $sparqlStatementStr .
                '}';
        } else {
            $sparql = '
                PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                INSERT DATA
                {
                    <http://phobia.vrinceanu.com/user#' . md5(uniqid() . time() . mt_rand()) .'>
                        foaf:knows <http://phobia.vrinceanu.com/user#' . $email .'>;
                        foaf:nickname "' . $uniqueID . '";
                        foaf:name "' . $user['name'] . '" .
               }';
        }

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_UPDATE);

        return $result;
    }

    /**
     * @param $email
     * @return mixed
     */
    public function getPersonsForUser($email)
    {
        $sparql = '
            PREFIX foaf:  <http://xmlns.com/foaf/0.1/>
            SELECT *
            WHERE {
                ?r foaf:name ?name .
                ?r foaf:nickname ?nickname .
                ?r foaf:knows <http://phobia.vrinceanu.com/user#' .$email .'> .
            }';

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_QUERY);
        $personsArr = $this->processPersonJsonString($result);
        foreach ($personsArr as $key => $person) {
            $phobias = $this->findPhobiasForUser($person['nickname'], 'foaf:nickname');
            $personsArr[$key]['phobias'] = $phobias;
        }

        return $personsArr;
    }

    /**
     * @param string $userJsonStr
     * @return array
     */
    private function processUserJsonString($userJsonStr)
    {
        $responseArr = json_decode($userJsonStr, true);
        $userRawArr = $responseArr['results']['bindings'];

        $userArr = [];
        foreach ($userRawArr as $user) {
            $userArr[] = [
                'id' => $user['person']['value'],
                'email' => $user['email']['value'],
                'password' => $user['password']['value'],
                'authToken' => null,
                'name' => $user['name']['value'],
            ];
        }

        return $userArr;
    }

    /**
     * @param $phobiaJsonStr
     * @return array
     */
    private function processUserPhobiasJsonString($phobiaJsonStr)
    {
        $responseArr = json_decode($phobiaJsonStr, true);
        $phobiaRawArr = $responseArr['results']['bindings'];

        $phobiaArr = [];
        foreach ($phobiaRawArr as $phobia) {
            $phobiaArr[] = $phobia['phobia']['value'];
        }

        return $phobiaArr;
    }

    /**
     * @param $personJsonStr
     * @return array
     */
    private function processPersonJsonString($personJsonStr)
    {
        $responseArr = json_decode($personJsonStr, true);
        $personRawArr = $responseArr['results']['bindings'];

        $personArr = [];
        foreach ($personRawArr as $person) {
            $personArr[] = [
                'id' => $person['r']['value'],
                'name' => $person['name']['value'],
                'nickname' => $person['nickname']['value'],
            ];
        }

        return $personArr;
    }
}
