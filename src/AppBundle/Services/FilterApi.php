<?php

namespace AppBundle\Services;

/**
 * Class FilterApi
 * @package AppBundle\Services
 */
class FilterApi
{
    /**
     * @var static
     */
    private $sortField;

    /**
     * @var string
     */
    private $sortDir;

    /**
     * @var integer
     */
    private $page;

    /**
     * @var integer
     */
    private $perPage;

    /**
     * @return mixed
     */
    public function getSortField()
    {
        return $this->sortField;
    }

    /**
     * @param mixed $sortField
     */
    public function setSortField($sortField)
    {
        $this->sortField = $sortField;
    }

    /**
     * @return mixed
     */
    public function getSortDir()
    {
        return $this->sortDir;
    }

    /**
     * @param mixed $sortDir
     */
    public function setSortDir($sortDir)
    {
        $this->sortDir = $sortDir;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @param mixed $perPage
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
    }
}
