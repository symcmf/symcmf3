<?php
namespace AuthBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\ORM\AbstractLoad;
use AuthBundle\Entity\Role;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load Project role
 */
class LoadRoles extends AbstractLoad
{
    /**
     * @param $object
     *
     * @return mixed
     */
    protected function createObject($object)
    {
        $role = new Role();
        $role->setName($object['name']);
        $role->setRole($object['role']);

        return $role;
    }

    /**
     * @return array
     */
    protected function getObjects()
    {
        return [Role::$userRole, Role::$adminRole];
    }

    /**
     * @param ObjectManager $manager
     * @param $object
     *
     * @return $object|null
     */
    protected function find(ObjectManager $manager, $object)
    {
        return $manager
            ->getRepository(Role::class)
            ->findOneBy(['role' => $object['role']]);
    }
}
