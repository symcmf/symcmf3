<?php

namespace AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AuthBundle\Entity\User;
use AuthBundle\Entity\Role;

/**
 * UserRole
 *
 * @ORM\Table(name="user_role")
 * @ORM\Entity(repositoryClass="AuthBundle\Repository\UserRoleRepository")
 */
class UserRole
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\ManyToOne(targetEntity="AuthBundle\Entity\User", inversedBy="roles")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AuthBundle\Entity\Role", inversedBy="users")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    protected $role;

    /**
     * Set user
     *
     * @param User $user
     *
     * @return UserRole
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set role
     *
     * @param Role $role
     *
     * @return UserRole
     */
    public function setRole(Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
