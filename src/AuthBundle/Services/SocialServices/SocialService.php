<?php

namespace AuthBundle\Services\SocialServices;

use AuthBundle\Entity\User;
use AppBundle\Services\AbstractService;
use AuthBundle\Services\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Class SocialService
 * @package AuthBundle\Services\SocialServices
 */
abstract class SocialService extends AbstractService
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * SocialService constructor.
     * @param EntityManager $entityManager
     * @param $session
     * @param UserService $userService
     */
    public function __construct(
        EntityManager $entityManager,
        Session $session,
        Router $router,
        DataCollectorTranslator $translator,
        UserService $userService
    )
    {
        parent::__construct($entityManager, $session, $router, $translator);
        $this->userService = $userService;
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
            return $this->userService->setAuth($user);
        }

        $name = $socialUser->getFirstName() . ' ' . $socialUser->getLastName();

        $user = new User();
        $user = $this->setSocialId($socialUser->getId(), $user);
        $user->setActivated(true);

        $user = $this->userService->addUser($user, $socialUser->getId(), $name, $socialUser->getEmail());

        if (!$user) {
            return false;
        }

        return $this->userService->setAuth($user);
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
