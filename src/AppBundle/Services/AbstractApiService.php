<?php

namespace AppBundle\Services;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class AbstractApiService
 * @package AppBundle\Services
 */
abstract class AbstractApiService extends AbstractService
{
    /**
     * @var integer
     */
    private $totalCount;

    /**
     * @return integer
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQuery()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('object')
            ->from($this->getClass(), 'object');

        return $qb;
    }

    /**
     * @param $childClass
     * @param $parent
     * @param $parentId
     *
     * @return QueryBuilder
     */
    private function getQueryChild($childClass, $parent, $parentId)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('object')
            ->from($childClass, 'object')
            ->where('object.' . $parent . '=' . $parentId);

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param FilterApi $filter
     *
     * @return array
     */
    private function filters(QueryBuilder $qb, FilterApi $filter)
    {
        $offset = ($filter->getPage() - 1) * $filter->getPerPage();

        if ($filter->getSortField() && $filter->getSortDir()) {
            $qb->orderBy('object.' . $filter->getSortField(), $filter->getSortDir());
        }

        $query = $qb->getQuery();
        $query
            ->setFirstResult($offset)
            ->setMaxResults($filter->getPerPage());

        $paginator = new Paginator($query);
        $this->totalCount = $paginator->count();

        return $query->getResult();
    }

    /**
     * @param FilterApi $filter
     * @return mixed
     */
    public function getList($filter)
    {
        return $this->filters($this->getQuery(), $filter);
    }

    /**
     * @param $parentField
     * @param $parentId
     * @param $filter
     *
     * @return array
     */
    public function getChildList($filter, $parentField, $parentId)
    {
        if (!$this->getChildClass()) {
            return [];
        }

        $qr = $this->getQueryChild($this->getChildClass(), $parentField, $parentId);
        return $this->filters($qr, $filter);
    }

    /**
     * @return string
     */
    abstract protected function getClass();

    /**
     * @return string|null
     */
    protected function getChildClass()
    {
        return null;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    abstract public function findById($id);
}
