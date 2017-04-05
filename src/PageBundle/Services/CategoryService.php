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
class CategoryService extends AbstractApiService
{
    /**
     * @return string
     */
    protected function getClass()
    {
        return Category::class;
    }

    /**
     * @return string
     */
    protected function getChildClass()
    {
        return Article::class;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function findById($id)
    {
        return $this->entityManager->getRepository($this->getClass())->find($id);
    }

    /**
     * @param $parentId
     * @param $childId
     *
     * @return null|object
     */
    public function findChildById($parentId, $childId)
    {
        $category =  $this->entityManager->getRepository($this->getClass())->find($parentId);

        if (!$category) {
            return null;
        }

        $article = $this
            ->entityManager
            ->getRepository($this->getChildClass())
            ->findOneBy([
                'category' => $category->getId(),
                'id' => $childId
            ]);

        return $article;
    }

    /**
     * @param $id
     * @param $cid
     */
    public function removeChildEntity($id, $cid)
    {
        $child = $this->findChildById($id, $cid);
        if (!$child) {
            throw new NotFoundHttpException(sprintf('Child (%d) not found', $cid));
        }

        $this->removeObject($child);
    }

    /**
     * @param $entity
     *
     * @return Category
     */
    public function saveCategory($entity)
    {
        $this->saveObject($entity);

        return $entity;
    }
}
