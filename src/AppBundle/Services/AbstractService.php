<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;

abstract class AbstractService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var
     */
    protected $container;

    /**
     * AbstractService constructor.
     * @param EntityManager $entityManager
     * @param $container
     */
    public function __construct(EntityManager $entityManager, $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * @param $object
     *
     * @return null
     *
     * @throws \Exception
     */
    protected function saveObject($object)
    {
        try {

            // Save
            $this->entityManager->persist($object);
            $this->entityManager->flush();

            return $object;

        } catch (\Exception $exception) {

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param $object
     *
     * @return null
     *
     * @throws \Exception
     */
    protected function updateObject($object)
    {
        try {

            // Update
            $this->entityManager->flush();

            return $object;

        } catch (\Exception $exception) {

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param $object
     * @throws \Exception
     */
    protected function removeObject($object)
    {
        try {

            // Remove
            $this->entityManager->remove($object);
            $this->entityManager->flush();

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}