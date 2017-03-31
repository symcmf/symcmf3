<?php

namespace AppBundle\Services;

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
     * @param $sortField
     * @param $sortDir
     * @param $page
     * @param $limit
     *
     * @return array
     */
    public function getList($sortField, $sortDir, $page, $limit)
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('object')
            ->from($this->getClass(), 'object');

        if ($sortField && $sortDir) {
            $qb->orderBy('object.' . $sortField, $sortDir);
        }

        $query = $qb->getQuery();
        $query
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query);
        $this->totalCount = $paginator->count();

        return $query->getResult();
    }

    /**
     * @return string
     */
    abstract protected function getClass();

    /**
     * @param $id
     *
     * @return mixed
     */
    abstract public function findById($id);
}
