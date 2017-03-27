<?php

namespace MessageBundle\Controller;

use AuthBundle\Entity\User;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use MessageBundle\Entity\MessageUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageUserAdminController extends BaseAdminController
{
    /**
     * Given a method name pattern, it looks for the customized version of that
     * method (based on the entity name) and executes it. If the custom method
     * does not exist, it executes the regular method.
     *
     * For example:
     *   executeDynamicMethod('create<EntityName>Entity') and the entity name is 'User'
     *   if 'createUserEntity()' exists, execute it; otherwise execute 'createEntity()'
     *
     * @param string $methodNamePattern The pattern of the method name (dynamic parts are enclosed with <> angle brackets)
     * @param array  $arguments         The arguments passed to the executed method
     *
     * @return mixed
     */
    private function executeDynamicMethod($methodNamePattern, array $arguments = array())
    {
        $methodName = str_replace('<EntityName>', $this->entity['name'], $methodNamePattern);

        if (!is_callable([$this, $methodName])) {
            $methodName = str_replace('<EntityName>', '', $methodNamePattern);
        }

        return call_user_func_array([$this, $methodName], $arguments);
    }

    /**
     * The method that is executed when the user performs a 'new' action on an entity.
     *
     * @return Response|RedirectResponse
     */
    protected function newAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_NEW);

        $entity = $this->executeDynamicMethod('createNew<EntityName>Entity');

        $easyadmin = $this->request->attributes->get('easyadmin');
        $easyadmin['item'] = $entity;
        $this->request->attributes->set('easyadmin', $easyadmin);

        $fields = $this->entity['new']['fields'];

        $newForm = $this->executeDynamicMethod('create<EntityName>NewForm', [$entity, $fields]);

        $newForm->handleRequest($this->request);
        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $this->dispatch(EasyAdminEvents::PRE_PERSIST, ['entity' => $entity]);

            $this->executeDynamicMethod('prePersist<EntityName>Entity', [$entity]);

            $this->em->persist($entity);
            $this->em->flush();

            $this->dispatch(EasyAdminEvents::POST_PERSIST, ['entity' => $entity]);

            $refererUrl = $this->request->query->get('referer', '');

            return !empty($refererUrl)
                ? $this->redirect(urldecode($refererUrl))
                : $this->redirect($this->generateUrl('easyadmin', ['action' => 'list', 'entity' => $this->entity['name']]));
        }

        $this->dispatch(EasyAdminEvents::POST_NEW, [
            'entity_fields' => $fields,
            'form' => $newForm,
            'entity' => $entity,
            'help' => $this->getHelper()
        ]);

        return $this->render($this->entity['templates']['new'], [
            'form' => $newForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
            'help' => $this->getHelper()
        ]);
    }

    /**
     * @return string
     */
    private function getHelper()
    {
        $metadata = $this->get('msg.service.message_service')->getAllowVariables();
        $helper = $this->get('translator')->trans('form.template.helper_message') . '<br>';
        foreach ($metadata as $field) {
            $helper .= ' {{ ' . $field    . ' }}' . ' / {{' . $field . '}} <br>';
        }
        return $helper;
    }

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
