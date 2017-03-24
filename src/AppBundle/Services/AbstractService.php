<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\DataCollectorTranslator;

abstract class AbstractService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var DataCollectorTranslator
     */
    protected $translator;

    /**
     * @var Router
     */
    protected $router;

    /**
     * AbstractService constructor.
     *
     * @param EntityManager $entityManager
     * @param Session $session
     * @param Router $router
     * @param DataCollectorTranslator $translator
     */
    public function __construct(
        EntityManager $entityManager,
        Session $session,
        Router $router,
        DataCollectorTranslator $translator
    )
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * @param $type
     * @param $message
     */
    protected function setFlashMessage($type, $message)
    {
        $this->session->getFlashBag()->add($type, $message);
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
            $this->setFlashMessage('error', $this->translator->trans('error_action_db', [
                '%process%' => 'saving',
            ]));
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
            $this->setFlashMessage('error', $this->translator->trans('error_action_db', [
                '%process%' => 'updating',
            ]));
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
            $this->setFlashMessage('error', $this->translator->trans('error_action_db', [
                '%process%' => 'removing',
            ]));
        }
    }
}
