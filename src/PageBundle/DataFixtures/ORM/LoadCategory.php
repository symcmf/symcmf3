<?php
namespace PageBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\ORM\AbstractLoad;
use Doctrine\Common\Persistence\ObjectManager;
use PageBundle\Entity\Category;

/**
 * Class LoadAdmin
 * @package PageBundle\DataFixtures\ORM
 */
class LoadCategory extends AbstractLoad
{
    /**
     * @param $object
     *
     * @return mixed
     */
    protected function createObject($object)
    {
        $category = new Category();

        $category->setName($object['name']);
        $category->setDescription($object['description']);
        $category->setActivated(true);

        return $category;
    }

    /**
     * @return array
     */
    protected function getObjects()
    {
        return [Category::$default];
    }

    /**
     * @param ObjectManager $manager
     * @param $object
     *
     * @return object
     */
    protected function find(ObjectManager $manager, $object)
    {
        return $manager
            ->getRepository(Category::class)
            ->findOneBy([
                'name' => $object['name']
            ]);
    }
}
