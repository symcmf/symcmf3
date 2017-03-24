<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserService extends AbstractService
{
    /**
     * @var
     */
    protected $encoder;

    /**
     * UserService constructor.
     *
     * @param EntityManager $entityManager
     * @param $passwordEncoder
     * @param $container
     */
    public function __construct(EntityManager $entityManager, $container,  $passwordEncoder)
    {
        parent::__construct($entityManager, $container);
        $this->encoder = $passwordEncoder;
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
            $this->container->get('security.token_storage')->setToken($token);
            $this->container->get('session')->set('_security_secured_area', serialize($token));

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

            $user = $this->findUserByEmail($email);

            if ($user) { return $user; }

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

        // Set role
        $user->setRole('ROLE_USER');

        return $this->saveObject($user);
    }
}
