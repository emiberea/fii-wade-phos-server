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

    public function insertUser($user)
    {
        $id = '2';
        $email = 'emi.berea2@gmail.com';
        $name = 'Emi Berea';
        $givenName = 'Berea Emanuel-Vasile';
        $password = 'password';

        $sparql = '
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            INSERT DATA
            {
                 <http://api.stardog.com/#' . $id .'>
                       foaf:name "' . $name . '";
                       foaf:mbox "' . $email .'";
                       foaf:givenName "' . $givenName . '";
                       foaf:sha1 "' . sha1($password) . '" .

           }';

        $result = $this->stardogService->executeStatement($sparql, StardogService::EXECUTE_UPDATE);

        return $result;
    }
}
