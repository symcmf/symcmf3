<?php

namespace PageBundle\Services;

use AppBundle\Services\AbstractApiService;
use PageBundle\Entity\Article;

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
     * @param $id
     *
     * @return null|object
     */
    public function findById($id)
    {
        return $this->entityManager->getRepository(Article::class)->find($id);
    }
}
