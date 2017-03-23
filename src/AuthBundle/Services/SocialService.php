<?php

namespace AuthBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Services\AbstractService;
use AppBundle\Services\UserService;
use Doctrine\ORM\EntityManager;

abstract class SocialService extends AbstractService
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var
     */
    protected $session;

    /**
     * SocialService constructor.
     * @param EntityManager $entityManager
     * @param UserService $userService
     * @param $session
     * @param $container
     */
    public function __construct(EntityManager $entityManager, $container, UserService $userService, $session)
    {
        parent::__construct($entityManager, $container);
        $this->userService = $userService;
        $this->session = $session;
    }

    /**
     * @param $socialUser
     *
     * @return bool
     */
    public function auth($socialUser)
    {

        $user = $this->findBySocialId($socialUser->getId());

        if ($user) {
            return $this->setAuth($user);
        }

        $name = $socialUser->getFirstName() . ' ' . $socialUser->getLastName();

        $user = new User();
        $user = $this->setSocialId($socialUser->getId(), $user);
        $user->setActivated(true);

        $user = $this->userService->addUser($user, $socialUser->getId(), $name, $socialUser->getEmail());

        if ($user) {
            return $this->userService->setAuth($user);
        }

        return false;
    }

    /**
     * @param $socialId
     *
     * @return User|null
     */
    protected abstract function findBySocialId($socialId);

    /**
     * @param $socialId
     * @param User $user
     *
     * @return mixed
     */
    protected abstract function setSocialId($socialId, User $user);

}
