<?php
namespace AuthBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\ORM\AbstractLoad;
use AuthBundle\Entity\Role;
use AuthBundle\Entity\User;
use AuthBundle\Entity\UserRole;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadAdmin
 * @package AuthBundle\DataFixtures\ORM
 */
class LoadAdmin extends AbstractLoad
{
    private $adminRole;

    /**
     * @param $object
     *
     * @return mixed
     */
    protected function createObject($object)
    {
        $user = new User();
        $userRole = new UserRole();
        $userRole->setUser($user);
        $userRole->setRole($this->adminRole);

        /** @var ObjectManager $em*/
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($userRole);
        $em->flush();

        $encoder = $this->container->get('security.password_encoder');

        $user->setEmail($object['email']);
        $user->setUsername($object['username']);

        $password = $encoder->encodePassword($user, $object['password']);

        $user->setPassword($password);
        $user->setActivated(true);
        $user->addRole($userRole);

        return $user;
    }

    /**
     * @return array
     */
    protected function getObjects()
    {
        return [User::$admin];
    }

    /**
     * @param ObjectManager $manager
     * @param $object
     *
     * @return mixed
     */
    protected function find(ObjectManager $manager, $object)
    {
        $this->adminRole = $manager
            ->getRepository(Role::class)->findOneBy(['role' => Role::$adminRole['role']]);

        return $manager->getRepository(User::class)->findOneByEmail($object['email']);
    }
}
