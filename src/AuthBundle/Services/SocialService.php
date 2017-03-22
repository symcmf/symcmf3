<?php

namespace AuthBundle\Services;


use AppBundle\Entity\User;
use AppBundle\Services\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class SocialService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var
     */
    protected $container;

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
    public function __construct(EntityManager $entityManager, UserService $userService, $session, $container)
    {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
        $this->session = $session;
        $this->container = $container;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    private function setAuth(User $user)
    {
        try {

            $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $this->session->set('_security_secured_area', serialize($token));

            return true;

        } catch (\Exception $exception) {

            return false;
        }
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

        $this->userService->addUser(
            $user,
            $socialUser->getId(),
            $name,
            $socialUser->getEmail()
        );

        if ($user) {
            return $this->setAuth($user);
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