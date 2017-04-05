<?php

namespace AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use AuthBundle\Entity\UserRole;

/**
 * Role
 *
 * @ORM\Table(name="role")
 * @ORM\Entity(repositoryClass="AuthBundle\Repository\RoleRepository")
 * @UniqueEntity(fields="role", message="Role with entered name already exist")
 */
class Role implements RoleInterface, \Serializable
{
    static public $userRole = [
        'role' => 'ROLE_USER',
        'name' => 'user',
    ];

    static public $adminRole = [
        'role' => 'ROLE_ADMIN',
        'name' => 'admin'
    ];

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
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    private $role;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="AuthBundle\Entity\UserRole", mappedBy="role")
     */
    private $users;

    public function __construct() {
        $this->users = new ArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the role.
     *
     * This method returns a string representation whenever possible.
     *
     * When the role cannot be represented with sufficient precision by a
     * string, it should return null.
     *
     * @return string|null A string representation of the role, or null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param $role
     *
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->name,
            $this->role,
        ));
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->name,
            $this->role,
            ) = unserialize($serialized);
    }

    /**
     * Add user
     *
     * @param UserRole $userRole
     *
     * @return Role
     */
    public function addUser(UserRole $userRole)
    {
        $this->users[] = $userRole;

        return $this;
    }

    /**
     * Remove user
     *
     * @param UserRole $userRole
     */
    public function removeUser(UserRole $userRole)
    {
        $this->users->removeElement($userRole);
    }
}
