<?php

namespace MessageBundle\Controller;

use AuthBundle\Entity\User;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use MessageBundle\Entity\MessageUser;

class MessageUserAdminController extends BaseAdminController
{
    /**
     * @param User $entity
     */
    public function prePersistEntity($entity)
    {
        $isFirst = false;
        $firstUser = null;


        $users = array_unique($entity->getUsers());
        foreach ($users as $user) {

            // first user will set for getting entity
            if (!$isFirst) {
                $isFirst = true;
                $firstUser = $user;
                continue;
            }

            $newEntity = new MessageUser();
            $newEntity->setMessage($entity->getMessage());
            $newEntity->setUser($user);

            if ($this->get('msg.service.message_service')->sendMessage($entity->getMessage(), $user)) {
                $this->em->persist($newEntity);
                $this->em->flush();
            }
        }

        // will add in newAction in controller BaseAdminController
        $entity->setUser($firstUser);
        $this->get('msg.service.message_service')->sendMessage($entity->getMessage(), $firstUser);
    }
}
