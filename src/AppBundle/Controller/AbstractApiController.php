<?php

namespace AppBundle\Controller;

use AppBundle\Services\FilterApi;
use Exception;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zend\Diactoros\Response\JsonResponse;

abstract class AbstractApiController extends FOSRestController
{
    /**
     * @param $id
     */
    private function throwNotFoundException($id)
    {
        // TODO need to user JSON api format.
        throw new NotFoundHttpException(sprintf($this->getType() . ' (%d) not found', $id));
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return FilterApi
     */
    private function getFilterParams(ParamFetcherInterface $paramFetcher)
    {
        $filter = $this->get('app.api_filter');

        $filter->setSortField($paramFetcher->get('_sortField'));
        $filter->setSortDir($paramFetcher->get('_sortDir'));
        $filter->setPage($paramFetcher->get('_page'));
        $filter->setPerPage($paramFetcher->get('_perPage'));

        return $filter;

    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getList(ParamFetcherInterface $paramFetcher)
    {
        $filter = $this->getFilterParams($paramFetcher);

        $objects = $this->getService()->getList($filter);
        $view = $this->view($objects, Response::HTTP_OK)->setHeader('X-Total-Count', $this->getService()->getTotalCount());

        return $objects;
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @param $parentField
     * @param $parentId
     *
     * @return Response
     */
    protected function getChildList(ParamFetcherInterface $paramFetcher, $parentField, $parentId)
    {
        $filter = $this->getFilterParams($paramFetcher);

        $objects = $this->getService()->getChildList($filter, $parentField, $parentId);
        $view = $this->view($objects, Response::HTTP_OK)->setHeader('X-Total-Count', $this->getService()->getTotalCount());

        return $this->handleView($view);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    protected function getEntity($id)
    {
        $object = $this->getService()->findById($id);
        if (!$object) {
            $this->throwNotFoundException($id);
        }

        return $object;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function postEntity(Request $request)
    {
        return $this->handleWriteTemplate($request);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return mixed
     */
    protected function putEntity(Request $request, $id)
    {
        return $this->handleWriteTemplate($request, $id);
    }

    /**
     * @param $id
     *
     * @return JsonResponse|static|View
     */
    protected function deleteEntity($id)
    {
        $object = $this->getService()->findById($id);

        if (!$object) {
            $this->throwNotFoundException($id);
        }

        try {

            $this->getEntityManager()->remove($object);
            $this->getEntityManager()->flush();

        } catch (Exception $e) {
            return View::create(['error' => $e->getMessage()], 400);
        }

    }

    /**
     * @return object
     */
    abstract protected function getService();

    /**
     * @return string
     */
    abstract protected function getType();

    /**
     * @param Request $request
     * @param null $id
     *
     * @return mixed
     */
    abstract protected function handleWriteTemplate(Request $request, $id = null);

}
