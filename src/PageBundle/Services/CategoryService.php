<?php

namespace PageBundle\Services;

use AppBundle\Services\AbstractService;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class CategoryService
 * @package PageBundle\Services
 */
class CategoryService extends AbstractService
{
    /**
     * @var integer
     */
    private $totalCount;

    /**
     * @param $sortField
     * @param $sortDir
     * @param $page
     * @param $limit
     *
     * @return array
     */
    public function getListOfEntity($sortField, $sortDir, $page, $limit)
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('category')
            ->from('PageBundle:Category', 'category');

        if ($sortField && $sortDir) {
            $qb->orderBy('category.' . $sortField, $sortDir);
        }

        $query = $qb->getQuery();
        $query->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query);
        $this->totalCount = $paginator->count();

        return $query->getResult();
    }

    /**
     * @return integer
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }
}
