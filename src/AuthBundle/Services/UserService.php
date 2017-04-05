<?php

namespace AuthBundle\Services;

use AppBundle\Services\AbstractApiService;
use AuthBundle\Entity\Role;
use AuthBundle\Entity\User;
use AuthBundle\Entity\UserRole;
use AuthBundle\Repository\UserRoleRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Class UserService
 * @package AuthBundle\Services
 */
class UserService extends AbstractApiService
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
     * @param null $username
     * @param null $email
     *
     * @return null
     */
    public function addUser(User $user, $socialId = null, $username = null, $email = null)
    {
        if ($email) {

            $result = $this->findUserByEmail($email);

            if ($result) { return $result; }

            $user->setEmail($email);
        }

        if ($username) { $user->setUsername($username); }

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

        $userRole = $this->saveUserRole($user, $role);

        // set role
        $user->addRole($userRole);

        return $this->saveObject($user);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function findById($id)
    {
        return $this->entityManager->getRepository($this->getClass())->find($id);
    }

    /**
     * @param $userId
     * @param $roleId
     *
     * @return null|object
     */
    public function getUserRoleById($userId, $roleId)
    {
        /** @var User $user */
        $user =  $this->entityManager->getRepository($this->getClass())->find($userId);

        /** @var Role $role */
        $role = $this->entityManager->getRepository($this->getChildClass())->find($roleId);

        if (!$user || !$role) {
            return null;
        }

        foreach ($user->getRoles() as $role) {
            if ($role->getId() == $roleId) {
                return $role;
            }
        }

        return null;
    }

    /**
     * @param $id
     * @return User|null
     */
    public function getUserById($id)
    {
        /** @var User $user */
        $user =  $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return null;
        }

        return $user;
    }

    /**
     * @param $id
     * @return Role|null
     */
    public function getRoleById($id)
    {
        /** @var Role $role */
        $role =  $this->entityManager->getRepository(Role::class)->find($id);

        if (!$role) {
            return null;
        }

        return $role;
    }

    /**
     * @param $userId
     * @param $roleId
     * @return bool
     */
    public function checkUserAndRole($userId, $roleId)
    {
        $user = $this->getUserById($userId);
        $role = $this->getRoleById($roleId);

        if (!$user || !$role) {
            throw new NotFoundHttpException('User or role not found');
        }

        return true;
    }

    /**
     * @param $parentField
     * @param $parentId
     * @param $filter
     * @param $manyToMany
     *
     * @return array
     */
    public function getChildList($filter, $parentField, $parentId, $manyToMany)
    {
        return parent::getChildList($filter, $parentField, $parentId, $manyToMany);
    }

    /**
     * @return string
     */
    protected function getClass()
    {
        return User::class;
    }

    /**
     * @return string
     */
    protected function getChildClass()
    {
        return Role::class;
    }

    /**
     * @param User $user
     * @param Role $role
     * @return UserRole
     */
    public function saveUserRole(User $user, Role $role)
    {
        $userRole = new UserRole();
        $userRole->setRole($role);
        $userRole->setUser($user);

        $this->saveObject($userRole);

        return $userRole;
    }

    /**
     * @param $id
     * @param $rid
     */
    public function removeUserRole($id, $rid)
    {
        $this->checkUserAndRole($id, $rid);

        /** @var UserRoleRepository $userRoleRepository */
        $userRoleRepository = $this->entityManager->getRepository(UserRole::class);
        $usersRoles = $userRoleRepository->getUserRoleByUserIdAndRoleId($id, $rid);

        foreach ($usersRoles as $userRole) {
            $this->entityManager->remove($userRole);
        }
        $this->entityManager->flush();
    }

    /**
     * @param $id
     * @param $rid
     * @return null
     */
    public function postUserRole($id, $rid)
    {
        $this->checkUserAndRole($id, $rid);

        /** @var User $user */
        $user =  $this->getUserById($id);

        /** @var Role $role */
        $role =  $this->getRoleById($rid);

        /** @var UserRoleRepository $userRoleRepository */
        $userRoleRepository = $this->entityManager->getRepository(UserRole::class);
        $usersRoles = $userRoleRepository->getUserRoleByUserIdAndRoleId($id, $rid);

        if ($usersRoles) {
            throw new BadRequestHttpException(sprintf('User with this roleId: (%d) already exists', $data['rid']));
        }

        $userRole = $this->saveUserRole($user, $role);
        $user->addRole($userRole);

        return $this->saveObject($user);
    }
}
