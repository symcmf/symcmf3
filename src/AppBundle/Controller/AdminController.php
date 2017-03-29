<?php

namespace AppBundle\Controller;

use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends BaseAdminController
{
    /**
     * @param $entity
     *
     * @return object mixed
     */
    protected function preUpdateChangeEntity($entity)
    {
        return $entity;
    }
    /**
     * It updates the value of some property of some entity to the new given value.
     *
     * @param mixed  $entity   The instance of the entity to modify
     * @param string $property The name of the property to change
     * @param bool   $value    The new value of the property
     *
     * @throws \RuntimeException
     */
    protected function updateEntityProperty($entity, $property, $value)
    {
        $entityConfig = $this->entity;

        // the method_exists() check is needed because Symfony 2.3 doesn't have isWritable() method
        if (method_exists($this->get('property_accessor'), 'isWritable') && !$this->get('property_accessor')->isWritable($entity, $property)) {
            throw new \RuntimeException(sprintf('The "%s" property of the "%s" entity is not writable.', $property, $entityConfig['name']));
        }

        $this->dispatch(EasyAdminEvents::PRE_UPDATE, array('entity' => $entity, 'newValue' => $value));

        $this->get('property_accessor')->setValue($entity, $property, $value);
        $this->em->persist($entity);
        $this->em->flush();

        $this->dispatch(EasyAdminEvents::POST_UPDATE, array('entity' => $entity, 'newValue' => $value));
        $this->dispatch(EasyAdminEvents::POST_EDIT);
    }

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
    protected function executeDynamicMethod($methodNamePattern, array $arguments = array())
    {
        $methodName = str_replace('<EntityName>', $this->entity['name'], $methodNamePattern);

        if (!is_callable(array($this, $methodName))) {
            $methodName = str_replace('<EntityName>', '', $methodNamePattern);
        }

        return call_user_func_array(array($this, $methodName), $arguments);
    }

    /**
     * The method that is executed when the user performs a 'edit' action on an entity.
     *
     * @return Response|RedirectResponse
     */
    protected function editAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_EDIT);
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        $this->executeDynamicMethod('preUpdateChange<EntityName>Entity', array($entity));
        $this->em->flush();

        if ($this->request->isXmlHttpRequest() && $property = $this->request->query->get('property')) {
            $newValue = 'true' === mb_strtolower($this->request->query->get('newValue'));
            $fieldsMetadata = $this->entity['list']['fields'];

            if (!isset($fieldsMetadata[$property]) || 'toggle' !== $fieldsMetadata[$property]['dataType']) {
                throw new \RuntimeException(sprintf('The type of the "%s" property is not "toggle".', $property));
            }

            $this->updateEntityProperty($entity, $property, $newValue);
            return new Response((string) $newValue);
        }

        $fields = $this->entity['edit']['fields'];

        $editForm = $this->executeDynamicMethod('create<EntityName>EditForm', array($entity, $fields));
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);
        $editForm->handleRequest($this->request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->dispatch(EasyAdminEvents::PRE_UPDATE, array('entity' => $entity));

            $this->executeDynamicMethod('preUpdate<EntityName>Entity', array($entity));
            $this->em->flush();

            $this->dispatch(EasyAdminEvents::POST_UPDATE, array('entity' => $entity));
            $refererUrl = $this->request->query->get('referer', '');

            return !empty($refererUrl)
                ? $this->redirect(urldecode($refererUrl))
                : $this->redirect($this->generateUrl('easyadmin', array('action' => 'list', 'entity' => $this->entity['name'])));
        }

        $this->dispatch(EasyAdminEvents::POST_EDIT);

        return $this->render($this->entity['templates']['edit'], array(
            'form' => $editForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }
}
