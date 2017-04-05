<?php

namespace AuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use AuthBundle\Entity\UserRole;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AuthBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="This email address is already in use")
 * @UniqueEntity(fields="facebookId", message="This facebook id is already in use")
 * @UniqueEntity(fields="googleId", message="This google id is already in use")
 * @UniqueEntity(fields="twitterId", message="This twitter id is already in use")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    static public $admin = [
        'username' => 'admin',
        'email' => 'admin@mail.com',
        'password' => 'admin',
    ];

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\OneToOne(targetEntity="UserToken", mappedBy="userId")
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
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40)
     */
    protected $username;

    /**
     * @ORM\OneToMany(targetEntity="AuthBundle\Entity\UserRole", mappedBy="user")
     */
    private $roles;

    /**
     * @Assert\Length(max=4096)
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     */
    protected $password;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $activated = false;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created;
    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated;

    /**
     * @param $activated
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;
    }

    /**
     * @return boolean
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $facebookId;

    /**
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param $facebookId
     *
     * @return $this
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $googleId;

    /**
     * @return mixed
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * @param $googleId
     *
     * @return $this
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $twitterId;

    /**
     * @param $twitterId
     *
     * @return $this
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * @return null
     */
    public function eraseCredentials()
    {
        return null;
    }

    /**
     * @var bool
     */
    private $forUserPanel = false;

    /**
     * Set flag for user panel
     */
    public function setUserPanel()
    {
        $this->forUserPanel = true;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        if ($this->forUserPanel) {
            return $this->roles->toArray();
        }

        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }

    /**
     * Add role
     *
     * @param UserRole $userRole
     *
     * @return User
     */
    public function addRole(UserRole $userRole)
    {
        if ($userRole->getUser()) {
            $userRole->setUser($this);
        }

        $this->roles[] = $userRole;

        return $this;
    }

    /**
     * Remove role
     *
     * @param UserRole $userRole
     */
    public function removeRole(UserRole $userRole)
    {
        $this->roles->removeElement($userRole);
    }

    /**
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updated = new \DateTime();
    }

    /**
     * Get activated
     *
     * @return boolean
     */
    public function getActivated()
    {
        return $this->activated;
    }
}
