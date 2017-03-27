<?php

namespace MessageBundle\Entity;

use AuthBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MessageUser
 *
 * @ORM\Table(name="message_user")
 * @ORM\Entity(repositoryClass="MessageBundle\Repository\MessageUserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MessageUser
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
     * MessageUser constructor.
     */
    public function __construct()
    {
        $this->users = [];
    }

    /**
     * @var MessageTemplate
     *
     * @ORM\ManyToOne(targetEntity="MessageTemplate")
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=false)
     *
     * @Assert\NotNull()
     */
    protected $message;

    /**
     * @return MessageTemplate,
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param MessageTemplate|null $message
     *
     * @return $this
     */
    public function setMessage(MessageTemplate $message = null)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AuthBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @var array
     */
    protected $users;

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param array $users
     * @return $this
     */
    public function setUsers($users)
    {
        foreach ($users as $user) {
            $this->users[] = $user['users'];
        }

        return $this;
    }

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated;

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
     * @return string
     */
    public function __toString()
    {
        $msg = $this->message ? $this->message->getSubject() : '<name>';
        return 'Message "' . $msg . '"';
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

}
