<?php

namespace AuthBundle\Services\TokenServices;

use AuthBundle\Entity\User;
use AppBundle\Services\AbstractService;
use AuthBundle\Services\UserService;
use AuthBundle\Entity\UserToken;
use Doctrine\ORM\EntityManager;
use MessageBundle\Services\Mailers\MailerService;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Class TokenService
 * @package AuthBundle\Services\TokenServices
 */
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
     * @param Session $session
     * @param UserService $userService
     * @param MailerService $mailer
     * @param Router $router
     * @param $keyForToken
     */
    public function __construct(
        EntityManager $entityManager,
        Session $session,
        Router $router,
        DataCollectorTranslator $translator,
        UserService $userService,
        MailerService $mailer,
        $keyForToken
    )
    {
        parent::__construct($entityManager, $session, $router, $translator);
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
            ->getRepository(UserToken::class)
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
            ->getRepository(UserToken::class)
            ->findOneBy(['token' => $token]);
    }

    /**
     * @param $user
     *
     * @return string
     */
    public function createUserToken(User $user)
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

        $userToken = $this->findUserTokenById($user->getId());

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

        $userToken = new UserToken();
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

        $user = $this->userService->activatedUserById($userToken->getUserId());
        $this->removeObject($userToken);

        return $user;
    }
}
