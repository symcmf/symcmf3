<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class UserService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var
     */
    protected $encoder;

    /**
     * @var
     */
    protected $container;

    /**
     * UserService constructor.
     *
     * @param EntityManager $entityManager
     * @param $passwordEncoder
     * @param $container
     */
    public function __construct(EntityManager $entityManager, $passwordEncoder, $container)
    {
        $this->entityManager = $entityManager;
        $this->encoder = $passwordEncoder;
        $this->container = $container;
    }

    /**
     * @param $object
     *
     * @return null
     */
    private function saveObject($object)
    {
        // TODO create abstract service for all entities with this function
        try {

            // Save
            $this->entityManager->persist($object);
            $this->entityManager->flush();

            return $object;

        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param $email
     *
     * @return null|User
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
     * @return null|object
     */
    public function findUserByFacebookId($id)
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['facebookId' => $id]);
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
        if ($name) {
            $user->setName($name);
        }

        if ($email) {
            $user->setEmail($email);
        }

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
