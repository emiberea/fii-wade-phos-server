<?php

namespace WADE\CoreBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class WebServiceUser implements UserInterface
{
    /** @var integer $id */
    private $id;

    /** @var string $email */
    private $email;

    /** @var string $password */
    private $password;

    /** @var string $authToken */
    private $authToken;

    /** @var string $name */
    private $name;

    /**
     * @param $id
     * @param $email
     * @param $password
     * @param $authToken
     * @param $name
     */
    public function __construct($id, $email, $password, $authToken, $name)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->authToken = $authToken;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param mixed $authToken
     * @return $this
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return null
     */
    public function eraseCredentials()
    {
        return null;
    }
}
