<?php

namespace PageBundle\Controller;

use AppBundle\Controller\AbstractApiController;
use Exception;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use NilPortugues\Symfony\JsonApiBundle\Serializer\JsonApiResponseTrait;
use PageBundle\Entity\Article;
use PageBundle\Entity\Category;
use PageBundle\Form\CategoryType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends AbstractApiController
{
    use JsonApiResponseTrait;

    /**
     * @return object
     */
    protected function getService()
    {
        return $this->get('page.service.category');
    }

    /**
     * @return string
     */
    protected function getType()
    {
        return 'category';
    }

    /**
     * @param $category
     *
     * @return Response
     */
    protected function getJsonApiResponse($category)
    {
        $serializer = $this->get('nil_portugues.serializer.json_api_serializer');

        /** @var \NilPortugues\Api\JsonApi\JsonApiTransformer $transformer */
        $transformer = $serializer->getTransformer();
        $transformer->setSelfUrl($this->generateUrl('get_categories', ['id' => $category->getId()], true));

        return $this->response($serializer->serialize($category));
    }

    /**
     * @ApiDoc(
     *  description="This is a description of your API method",
     * )
     * List all users.
     *
     * @QueryParam(name="_page", requirements="\d+", default=1, nullable=true, description="Page number.")
     * @QueryParam(name="_perPage", requirements="\d+", default=30, nullable=true, description="Limit.")
     * @QueryParam(name="_sortField", nullable=true, description="Sort field.")
     * @QueryParam(name="_sortDir", nullable=true, description="Sort direction.")
     *
     * @param Request $request the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getCategoriesAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        return parent::getList($paramFetcher);
    }

    /**
     * Retrieves a specific category.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="category id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when category is not found"
     *  }
     * )
     *
     * @Symfony\Component\Routing\Annotation\Route("/categories/{id}", name="get_categories")
     * @Sensio\Bundle\FrameworkExtraBundle\Configuration\Method({"GET"})
     *
     * @param $id
     *
     * @return Category
     *
     * @throws NotFoundHttpException
     */
    public function getCategoryAction($id)
    {
        return parent::getEntity($id);
    }

    /**
     * Adds a category.
     *
     * @ApiDoc(
     *   input = {
     *      "class" = "PageBundle\Form\CategoryType",
     *      "options" = {"method" = "POST"},
     *      "name" = ""
     *   },
     *  output={ "class"="PageBundle\Entity\Category" },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while category creation",
     *  }
     * )
     *
     * @param Request $request A Symfony request
     *
     * @return Category|Form
     *
     * @throws NotFoundHttpException
     */
    public function postCategoryAction(Request $request)
    {
        return parent::postEntity($request);
    }


    /**
     * Updates a category
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="category id"},
     *  },
     *  input = {
     *      "class" = "PageBundle\Form\CategoryType",
     *      "name" = ""
     *   },
     *  output={ "class"="PageBundle\Entity\Category" },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while updating the category",
     *      404="Returned when unable to find the message template"
     *  }
     * )
     *
     * @param int $id A category template identifier
     * @param Request $request A Symfony request
     *
     * @return Category
     *
     * @throws NotFoundHttpException
     */
    public function putCategoryAction($id, Request $request)
    {
        return parent::putEntity($request, $id);
    }

    /**
     * Deletes a category
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="category id"}
     *  },
     *  statusCodes={
     *      200="Returned when category is successfully deleted",
     *      400="Returned when an error has occurred while category deletion",
     *      404="Returned when unable to find category"
     *  }
     * )
     *
     * @param int $id A category identifier
     *
     * @return View|JsonResponse
     *
     * @throws NotFoundHttpException
     */
    public function deleteCategoryAction($id)
    {
        return parent::deleteEntity($id);
    }

    /**
     * Retrieves a specific article.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="category id"},
     *      {"name"="aid", "dataType"="integer", "requirement"="\d+", "description"="article id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when article is not found"
     *  }
     * )
     *
     * @param $id
     * @param $aid
     *
     * @return Article
     *
     * @throws NotFoundHttpException
     */
    public function getCategoryArticleAction($id, $aid)
    {
        $article = $this->getService()->findChildById($id, $aid);

        if (!$article) {
            throw new NotFoundHttpException(sprintf('Child Article (%d) not found', $aid));
        }

        return $article;
    }

    /**
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="category id"}
     *  },
     *  description="This is a description of your API method",
     * )
     * List all users.
     *
     * @QueryParam(name="_page", requirements="\d+", default=1, nullable=true, description="Page number.")
     * @QueryParam(name="_perPage", requirements="\d+", default=30, nullable=true, description="Limit.")
     * @QueryParam(name="_sortField", nullable=true, description="Sort field.")
     * @QueryParam(name="_sortDir", nullable=true, description="Sort direction.")
     *
     * @param $id
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getCategoryArticlesAction($id, ParamFetcherInterface $paramFetcher)
    {
        $category = $this->getService()->findById($id);
        return parent::getChildList($paramFetcher, 'category', $category->getId());
    }

    /**
     * Deletes a category
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="category id"},
     *      {"name"="aid", "dataType"="integer", "requirement"="\d+", "description"="article id"}
     *  },
     *  statusCodes={
     *      200="Returned when category is successfully deleted",
     *      400="Returned when an error has occurred while article deletion",
     *      404="Returned when unable to find category"
     *  }
     * )
     *
     * @param int $id A category identifier
     * @param int $aid
     *
     * @return View|JsonResponse
     *
     * @throws NotFoundHttpException
     */
    public function deleteCategoryArticleAction($id, $aid)
    {
        $article = $this->getService()->findChildById($id, $aid);

        if (!$article) {
            throw new NotFoundHttpException(sprintf('Child Article (%d) not found', $aid));
        }

        try {

            $this->getEntityManager()->remove($article);
            $this->getEntityManager()->flush();

        } catch (Exception $e) {
            return View::create(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @param null $id
     *
     * @return mixed
     */
    protected function handleWriteTemplate(Request $request, $id = null)
    {
        $category = $id ? $this->getService()->findById($id) : new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isValid()) {

            $category = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $category;
        }

        return $form;
    }

    // TODO need to create trait or abstract class to remove this functions
    /**
     * @param $formErrors
     *
     * @return JsonResponse
     */
    private function jsonApiValidationErrors($formErrors)
    {
        $errors = [];
        foreach ($formErrors as $error) {
            $row = [];

            if ($error->getOrigin()) {
                $row['status'] = Response::HTTP_UNPROCESSABLE_ENTITY;

                $source['pointer'] = '/data/attributes/' . $error->getOrigin()->getName();
                $row['source'] = $source;

                $row['title'] = 'Invalid Attribute';
                $row['detail'] = $error->getMessage();

                $errors[] = $row;
            }
        }

        $body['errors'] = $errors;
        return new JsonResponse($body, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param $type
     * @param $id
     *
     * @return JsonResponse
     */
    private function jsonApiNotFoundError($type, $id)
    {
        $body['errors'] = [
            'id' => $id,
            'status' => Response::HTTP_NOT_FOUND,
            'title' => $type . ' not found',
            'detail' => $type . ' ' . $id . 'is not available on this server',
        ];
        return new JsonResponse($body, Response::HTTP_NOT_FOUND);
    }
}
