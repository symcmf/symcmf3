<?php

namespace AuthBundle\Controller;

use AuthBundle\Entity\User;
use AppBundle\Controller\AdminController as BaseAdminController;

class UserAdminController extends BaseAdminController
{
    /**
     * @param $entity
     *
     * @return User $entity
     */
    protected function preUpdateChangeEntity(User $entity)
    {
        return $entity->setUserPanel();

    }

    /**
     * @param User $entity
     */
    public function prePersistEntity($entity)
    {
        $password = $this->get('security.password_encoder')->encodePassword($entity, $entity->getPassword());
        $entity->setPassword($password);
    }
}
