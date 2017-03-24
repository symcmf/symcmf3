<?php

namespace AuthBundle\Services\TokenServices;

use AppBundle\Entity\User;
use AppBundle\Entity\UserActivations;
use AppBundle\Services\AbstractService;
use AppBundle\Services\UserService;
use Doctrine\ORM\EntityManager;
use MessageBundle\Services\Mailers\MailerService;

class TokenService extends AbstractService
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var MailerService
     */
    protected $mailer;

    /**
     * @var string
     */
    private $keyForToken;

    /**
     * TokenService constructor.
     *
     * @param EntityManager $entityManager
     * @param $container
     * @param UserService $userService
     * @param MailerService $mailer
     * @param $keyForToken
     */
    public function __construct(
        EntityManager $entityManager,
        $container,
        UserService $userService,
        MailerService $mailer,
        $keyForToken
    )
    {
        parent::__construct($entityManager, $container);
        $this->userService = $userService;
        $this->mailer = $mailer;
        $this->keyForToken = $keyForToken;
    }

    /**
     * @return string - token
     */
    protected function getToken()
    {
        return hash_hmac('sha256', random_bytes(40), $this->keyForToken);
    }

    /**
     * @param $id
     *
     * @return null|object
     */
    protected function findUserTokenById($id)
    {
        return $this->entityManager
            ->getRepository(UserActivations::class)
            ->findOneBy(['userId' => $id]);
    }

    /**
     * @param $token
     *
     * @return null|object
     */
    protected function findUserTokenByToken($token)
    {
        return $this->entityManager
            ->getRepository(UserActivations::class)
            ->findOneBy(['token' => $token]);
    }

    /**
     * @param $user
     *
     * @return string
     */
    public function createConfirmation(User $user)
    {
        $userToken = $this->findUserTokenById($user->getId());
        if (!$userToken) {
            return $this->createToken($user);
        }
        return $this->regenerateToken($user);
    }

    /**
     * @param $user
     *
     * @return string
     */
    private function regenerateToken($user)
    {
        $token = $this->getToken();

        $userToken = $this->findUserTokenById($user->id);

        if ($userToken) {

            $userToken->setToken($token);
            $userToken->setUpdated(new \DateTime());

            $this->updateObject($userToken);

            return $token;
        }

        return '';
    }

    /**
     * Function to install new row in table
     *
     * @param $user
     * @return string
     */
    private function createToken($user)
    {
        $token = $this->getToken();

        // TODO change to UserToken
        $userToken = new UserActivations();
        $userToken->setUserId($user);
        $userToken->setToken($token);

        $this->saveObject($userToken);

        return $token;
    }

    /**
     * @param $token
     *
     * @return User
     */
    public function removeUserToken($token)
    {
        $userToken = $this->findUserTokenByToken($token);

        if (!$userToken) {
            return null;
        }

        // TODO maybe need to delete it from here
        $user = $this->userService->activatedUserById($userToken->getUserId());
        $this->removeObject($userToken);

        return $user;
    }
}