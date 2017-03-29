<?php
namespace AuthBundle\DataFixtures\ORM;

use AuthBundle\Entity\Role;
use AuthBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadAdmin
 * @package AuthBundle\DataFixtures\ORM
 */
class LoadAdmin implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $userInformation
     * @param $adminRole
     *
     * @return User
     */
    private function createUser($userInformation, $adminRole)
    {
        $user = new User();

        $encoder = $this->container->get('security.password_encoder');

        $user->setEmail($userInformation['email']);
        $user->setName($userInformation['name']);

        $password = $encoder->encodePassword($user, $userInformation['password']);

        $user->setPassword($password);
        $user->setActivated(true);
        $user->addRole($adminRole);

        return $user;
    }

    /**
     * @param ObjectManager $manager
     * @param $user
     */
    private function addUser(ObjectManager $manager, $user)
    {
        $manager->persist($user);
        $manager->flush();
    }

    /**
     * Load default role to db
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $repository = $manager->getRepository(User::class);

        $adminRole = $manager->getRepository(Role::class)->findOneBy(['role' => Role::$adminRole['role']]);

        $user = $repository->findOneByEmail(User::$admin['email']);
        if (!$user) {
            $this->addUser($manager, $this->createUser(User::$admin, $adminRole));
        }
    }
}
