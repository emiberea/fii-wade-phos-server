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

    public function findUserByEmail($user)
    {
        $sparql = '
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            INSERT DATA
            {
                 <http://api.stardog.com/#' . $user['id'] .'>
                       foaf:mbox "' . $user['email'] .'";
                       foaf:name "' . $user['name'] . '";
                       foaf:sha1 "' . sha1($user['password']) . '" .

           }';

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_QUERY);

        return $result;
    }

    /**
     * @param $user
     * @return mixed
     */
    public function insertUser($user)
    {
        $sparql = '
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            INSERT DATA
            {
                 <http://api.stardog.com/#' . $user['id'] .'>
                       foaf:mbox "' . $user['email'] .'";
                       foaf:name "' . $user['name'] . '";
                       foaf:sha1 "' . sha1($user['password']) . '" .

           }';

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_UPDATE);

        return $result;
    }
}
