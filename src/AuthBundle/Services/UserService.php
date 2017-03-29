<?php

namespace AuthBundle\Services;

use AppBundle\Services\AbstractService;
use AuthBundle\Entity\Role;
use AuthBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Class UserService
 * @package AuthBundle\Services
 */
class UserService extends AbstractService
{
    /**
     * @var UserPasswordEncoder
     */
    protected $encoder;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * UserService constructor.
     *
     * @param EntityManager $entityManager
     * @param Session $session
     * @param Router $router
     * @param DataCollectorTranslator $translator
     * @param UserPasswordEncoder $passwordEncoder
     * @param TokenStorage $tokenStorage
     */
    public function __construct(
        EntityManager $entityManager,
        Session $session,
        Router $router,
        DataCollectorTranslator $translator,
        UserPasswordEncoder $passwordEncoder,
        TokenStorage $tokenStorage
    )
    {
        parent::__construct($entityManager, $session, $router, $translator);
        $this->encoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function setAuth(User $user)
    {
        try {

            $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->tokenStorage->setToken($token);
            $this->session->set('_security_secured_area', serialize($token));

            return true;

        } catch (\Exception $exception) {

            return false;
        }
    }

    /**
     * @param $id
     *
     * @return User|null
     */
    public function findUserById($id)
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->find($id);
    }

    /**
     * @param $email
     *
     * @return User|null
     */
    public function findUserByEmail($email)
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    /**
     * @param $id
     *
     * @return User|null
     */
    public function findUserByFacebookId($id)
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['facebookId' => $id]);
    }

    /**
     * @param User $user
     *
     * @return User|null
     */
    public function changePassword(User $user)
    {
        $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        return $this->updateObject($user);
    }

    /**
     * @param $id
     *
     * @return User|null
     */
    public function activatedUserById($id)
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) { return null; }

        $user->setActivated(true);

        return $this->updateObject($user);
    }

    /**
     * @param User $user
     * @param null $socialId
     * @param null $name
     * @param null $email
     *
     * @return null
     */
    public function addUser(User $user, $socialId = null, $name = null, $email = null)
    {
        if ($email) {

            $result = $this->findUserByEmail($email);

            if ($result) { return $result; }

            $user->setEmail($email);
        }

        if ($name) { $user->setName($name); }

        // Encode the new users password
        if ($socialId) {
            $password = $this->encoder->encodePassword($user, $socialId);
        } else {
            $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
        }

        $user->setPassword($password);

        $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => 'user']);

        if (!$role) {
            $role = new Role();
            $role->setName(Role::$userRole['name']);
            $role->setRole(Role::$userRole['role']);

            $this->saveObject($role);
        }

        // set role
        $user->addRole($role);

        return $this->saveObject($user);
    }
}
