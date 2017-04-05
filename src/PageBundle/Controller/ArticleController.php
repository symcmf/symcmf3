<?php

namespace PageBundle\Controller;

use AppBundle\Controller\AbstractApiController;
use Doctrine\Common\Util\ClassUtils;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use NilPortugues\Symfony\JsonApiBundle\Serializer\JsonApiResponseTrait;
use PageBundle\Entity\Article;
use PageBundle\Entity\Category;
use PageBundle\Form\ArticleType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ArticleController
 * @package PageBundle\Controller
 */
class ArticleController extends AbstractApiController
{
    /**
     * @return object
     */
    protected function getService()
    {
        return $this->get('page.service.article');
    }

    /**
     * @return string
     */
    protected function getType()
    {
        return 'article';
    }

    /**
     *
     * @ApiDoc(
     *     section = "Articles",
     *     description="List of articles",
     *     statusCodes={
     *          200="Returned list of articles",
     *     }
     * )
     *
     * List all articles.
     *
     * @QueryParam(name="_page", requirements="\d+", default=1, nullable=true, description="Page number.")
     * @QueryParam(name="_perPage", requirements="\d+", default=30, nullable=true, description="Limit.")
     * @QueryParam(name="_sortField", nullable=true, description="Sort field.")
     * @QueryParam(name="_sortDir", nullable=true, description="Sort direction.")
     *
     * @param Request $request the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Get("/articles")
     *
     * @return array
     */
    public function getArticlesAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        return parent::getList($paramFetcher);
    }

    /**
     * Retrieves a specific article.
     *
     * @ApiDoc(
     *  section = "Articles",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="article id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when article is not found"
     *  }
     * )
     *
     * @Get("/articles/{id}")
     *
     * @param $id
     *
     * @return Article
     *
     * @throws NotFoundHttpException
     */
    public function getArticleAction($id)
    {
        return parent::getEntity($id);
    }

    /**
     * Adds an article.
     *
     * @ApiDoc(
     *   section = "Articles",
     *   input = {
     *      "class" = "PageBundle\Form\ArticleType",
     *      "options" = {"method" = "POST"},
     *      "name" = ""
     *   },
     *  output={ "class"="PageBundle\Entity\Article" },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while article creation",
     *  }
     * )
     *
     * @Post("/articles")
     *
     * @param Request $request A Symfony request
     *
     * @return Article|Form
     *
     * @throws NotFoundHttpException
     */
    public function postArticleAction(Request $request)
    {
        return parent::postEntity($request);
    }


    /**
     * Updates a category
     *
     * @ApiDoc(
     *  section = "Articles",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="article id"},
     *  },
     *  input = {
     *      "class" = "PageBundle\Form\ArticleType",
     *      "name" = ""
     *   },
     *  output={ "class"="PageBundle\Entity\Article" },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while updating the article",
     *      404="Returned when unable to find the article"
     *  }
     * )
     *
     * @Put("/articles/{id}")
     *
     * @param int $id A category template identifier
     * @param Request $request A Symfony request
     *
     * @return Article
     *
     * @throws NotFoundHttpException
     */
    public function putArticleAction($id, Request $request)
    {
        return parent::putEntity($request, $id);
    }

    /**
     * Deletes a category
     *
     * @ApiDoc(
     *  section = "Articles",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="article id"}
     *  },
     *  statusCodes={
     *      200="Returned when article is successfully deleted",
     *      400="Returned when an error has occurred while article deletion",
     *      404="Returned when unable to find article"
     *  }
     * )
     *
     * @Delete("/articles/{id}")
     *
     * @param int $id A category identifier
     *
     * @return View|JsonResponse
     *
     * @throws NotFoundHttpException
     */
    public function deleteArticleAction($id)
    {
        return parent::deleteEntity($id);
    }

    /**
     * Retrieves a category related to article.
     *
     * @ApiDoc(
     *  section = "Articles",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="article id"}
     *  },
     *  statusCodes={
     *          200="Returned list of categories",
     *  }
     * )
     *
     * @Get("/articles/{id}/category")
     *
     * @param $id
     *
     * @return Category
     *
     * @throws NotFoundHttpException
     */
    public function getArticleCategoryAction($id)
    {
        $article = parent::getEntity($id);

        return $article->getCategory();
    }

    /**
     * Update a category related to article (add another existed category by id)
     *
     * @ApiDoc(
     *  section = "Articles",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="article id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="category id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when article or category not found"
     *  }
     * )
     *
     * @Put("/articles/{id}/categories/{cid}")
     *
     * @param $id
     *
     * @return Category
     *
     * @throws NotFoundHttpException
     */
    public function putArticleCategoryAction($id, $cid)
    {
        $article = $this->getService()->findById($id);
        $category = $this->getDoctrine()->getRepository(Category::class)->find($cid);

        if (!$category) {
            throw new NotFoundHttpException(sprintf('Category (%d) not found', $cid));
        }

        $article->setCategory($category);

        $this->getDoctrine()->getManager()->persist($article);
        $this->getDoctrine()->getManager()->flush();

        return $article;
    }

    /**
     * @param Request $request
     * @param null $id
     *
     * @return mixed
     */
    protected function handleWriteTemplate(Request $request, $id = null)
    {
        $article = $id ? $this->getService()->findById($id) : new Article();

        $category = $article->getCategory();

        if ($category) {
            $categoryId = $category->getId();
            $article->setCategory($categoryId);
        }

        $form = $this->createForm(ArticleType::class, $article);

        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isValid()) {

            $categoryId = $request->get('category');

            $category = $this->getDoctrine()->getRepository(Category::class)->find($categoryId);
            if (!$category) {
                throw new NotFoundHttpException(sprintf('Category (%d) not found ', $categoryId));
            }

            $article = $form->getData();
            $article->setCategory($category);

            $this->getEntityManager()->persist($article);
            $this->getEntityManager()->flush();

            return $article;
        }

        return $form;
    }
}
