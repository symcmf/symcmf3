<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractLoad
 * @package AppBundle\DataFixtures\ORM
 */
abstract class AbstractLoad implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     * @param $object
     */
    protected function addObject(ObjectManager $manager, $object)
    {
        $manager->persist($object);
        $manager->flush();
    }

    /**
     * Load default role to db
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $array = $this->getObjects();
        foreach ($array as $information)
        {
            $object = $this->find($manager, $information);
            if (!$object) {
                $this->addObject($manager, $this->createObject($information));
            }
        }
    }

    /**
     * @param $object
     *
     * @return $object
     */
    abstract protected function createObject($object);

    /**
     * @return array
     */
    abstract protected function getObjects();

    /**
     * @param ObjectManager $manager
     * @param $object
     *
     * @return $object|null
     */
    abstract protected function find(ObjectManager $manager, $object);
}
