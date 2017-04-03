<?php

namespace PageBundle\Services;

use AppBundle\Services\AbstractApiService;
use PageBundle\Entity\Article;
use PageBundle\Entity\Category;

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
}
