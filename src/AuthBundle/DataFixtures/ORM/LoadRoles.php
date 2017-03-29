<?php
namespace AuthBundle\DataFixtures\ORM;

use AuthBundle\Entity\Role;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load Project role
 */
class LoadRoles implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $roleInformation
     * @return Role
     */
    private function createRole($roleInformation)
    {
        $role = new Role();
        $role->setName($roleInformation['name']);
        $role->setRole($roleInformation['role']);

        return $role;
    }

    /**
     * @param ObjectManager $manager
     * @param $role
     */
    private function addRole(ObjectManager $manager, $role)
    {
        $manager->persist($role);
        $manager->flush();
    }

    /**
     * Load default role to db
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $repository = $manager->getRepository(Role::class);

        $role = $repository->findOneBy(['role' => Role::$userRole['role']]);
        if (!$role) {
            $this->addRole($manager, $this->createRole(Role::$userRole));
        }

        $role = $repository->findOneBy(['role' => Role::$adminRole['role']]);
        if (!$role) {
            $this->addRole($manager, $this->createRole(Role::$adminRole));
        }
    }
}
