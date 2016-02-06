<?php

namespace WADE\CoreBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use WADE\CoreBundle\Manager\UserManager;

class WebServiceUserProvider implements UserProviderInterface
{
    /** @var UserManager $userManger */
    private $userManger;

    /**
     * @param UserManager $userManger
     */
    public function __construct(UserManager $userManger)
    {
        $this->userManger = $userManger;
    }

    /**
     * @param string $username
     * @return WebServiceUser
     * @throws \Exception
     */
    public function loadUserByUsername($username)
    {
        $userData = $this->userManger->findUserByEmail($username);
        if ($userData) {
            return new WebServiceUser($userData['id'], $userData['email'], $userData['password'], $userData['authToken'], $userData['name']);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    /**
     * @param UserInterface $user
     * @return UserInterface|void
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof WebServiceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'WADE\CoreBundle\Security\User\WebServiceUser';
    }
}
