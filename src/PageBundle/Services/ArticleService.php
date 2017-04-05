<?php

namespace PageBundle\Services;

use AppBundle\Services\AbstractApiService;
use PageBundle\Entity\Article;
use PageBundle\Entity\Category;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CategoryService
 * @package PageBundle\Services
 */
class ArticleService extends AbstractApiService
{
    /**
     * @return string
     */
    protected function getClass()
    {
        return Article::class;
    }

    /**
     * @return string
     */
    protected function getChildClass()
    {
        return Category::class;
    }

    /**
     * @param $id
     * @return null|object
     */
    public function findById($id)
    {
        return $this->entityManager->getRepository(Article::class)->find($id);
    }

    /**
     * @param $entity
     * @param $childId
     *
     * @return mixed
     *
     * @throws NotFoundHttpException
     */
    public function saveArticle($entity, $childId)
    {
        $category = $this->getChildEntity($childId);
        $entity->setCategory($category);

        $this->saveObject($entity);

        return $entity;
    }

    /**
     * @param $parentId
     * @param $childId
     *
     * @return null|object
     */
    public function updateChildEntity($parentId, $childId)
    {
        $parent = $this->findById($parentId);
        $child = $this->getChildEntity($childId);

        $parent->setCategory($child);

        $this->updateObject($parent);

        return $parent;
    }
}
