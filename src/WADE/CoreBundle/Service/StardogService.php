<?php

namespace WADE\CoreBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class StardogService
{
    const EXECUTE_QUERY = 'query';
    const EXECUTE_UPDATE = 'update';

    private static $statementTypeArr = array(
        self::EXECUTE_QUERY,
        self::EXECUTE_UPDATE,
    );

    /** @var ContainerInterface $container */
    private $container;

    /** @var string $username */
    private $username;

    /** @var string $password */
    private $password;

    /** @var string $host */
    private $host;

    /** @var string $port */
    private $port;

    /** @var string $databaseName */
    private $databaseName;

    /** @var string $baseUrl */
    private $baseUrl;

    /**
     * @param ContainerInterface $container
     * @param $username
     * @param $password
     * @param $host
     * @param $port
     * @param $databaseName
     */
    public function __construct(ContainerInterface $container, $username, $password, $host, $port, $databaseName)
    {
        $this->container = $container;
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->databaseName = $databaseName;
        $this->baseUrl = "http://{$this->username}:{$this->password}@{$this->host}:{$this->port}/{$this->databaseName}/";
    }

    /**
     * @param $sparql
     * @param string $statementType
     * @return mixed
     */
    public function executeStatement($sparql, $statementType = self::EXECUTE_QUERY)
    {
        if (!in_array($statementType, self::$statementTypeArr)) {
            throw new \InvalidArgumentException();
        }

        // build header
        $header = array();
        $header[] = "Content-Type: application/x-www-form-urlencoded";
        if ($statementType == self::EXECUTE_QUERY) {
            $header[] = "Accept: application/sparql-results+json";
        }

        // curl request
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->baseUrl}{$statementType}", // this is the base URL concatenated with query or update
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'query' => $sparql,
            )),
        ));

        $curlResponse = curl_exec($curl);
        $curlErr = curl_error($curl);
        $curlErrNo = curl_errno($curl);

        // log curl response
        $this->container->get('logger')->addCritical(
            "StarDog: cURL \$curlResp: " . print_r($curlResponse, true)
        );
        $this->container->get('logger')->addCritical(
            "StarDog: cURL \$curlErr: " . print_r($curlErr, true)
        );
        $this->container->get('logger')->addCritical(
            "StarDog: cURL \$curlErrNo: " . print_r($curlErrNo, true)
        );

        // TODO: remove
//        var_dump($curlResponse);
//        var_dump($curlErr);
//        var_dump($curlErrNo);
//        die;
        return $curlResponse;
    }
}
