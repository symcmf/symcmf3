<?php

namespace AppBundle\Services;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param $manyToMany
     *
     * @return QueryBuilder
     */
    private function getQueryChild($childClass, $parent, $parentId, $manyToMany)
    {
        $qb = $this->entityManager->createQueryBuilder();
        if ($manyToMany) {
            $qb
                ->select('classFrom')
                ->from($manyToMany['classFrom'], 'classFrom')
                ->innerJoin(
                    $manyToMany['classJoin'], 'classJoin',
                    'WITH',
                    'classFrom.' . $manyToMany['classFromField'] . '=' . 'classJoin.' . $manyToMany['classJoinField']
                )
                ->where('classFrom.' . $manyToMany['fieldForWhere'] . '=' . $parentId);
        } else {
            $qb
                ->select('classFrom')
                ->from($childClass, 'classFrom')
                ->where('classFrom.' . $parent . '=' . $parentId);
        }

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
     * @param $manyToMany
     *
     * @return array
     */
    public function getChildList($filter, $parentField, $parentId, $manyToMany)
    {
        if (!$this->getChildClass()) {
            return [];
        }

        $qr = $this->getQueryChild($this->getChildClass(), $parentField, $parentId, $manyToMany);
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
     * @param $childId
     *
     * @return null|object
     */
    public function getChildEntity($childId)
    {
        if ($this->getChildClass()) {
            $child =  $this->entityManager->getRepository($this->getChildClass())->find($childId);
            if (!$child) {
                throw new NotFoundHttpException(sprintf('Child object (%d) not found ', $childId));
            }

            return $child;
        }

        return null;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    abstract public function findById($id);
}
