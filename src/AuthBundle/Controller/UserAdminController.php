<?php

namespace AuthBundle\Controller;

use AuthBundle\Entity\User;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class UserAdminController extends BaseAdminController
{
    /**
     * @param User $entity
     */
    public function prePersistEntity($entity)
    {
        $password = $this->get('security.password_encoder')->encodePassword($entity, $entity->getPassword());
        $entity->setPassword($password);
    }
}
