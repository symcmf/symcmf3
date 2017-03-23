<?php

namespace AuthBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\UserActivations;
use AppBundle\Services\AbstractService;
use AppBundle\Services\UserService;
use Doctrine\ORM\EntityManager;
use MessageBundle\Services\Mailers\MailerService;

class ActivationService extends AbstractService
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

    const CONFIRMATION_SUBJECT = 'Confirmation message';

    /**
     * ActivationService constructor.
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
    }

    /**
     * @param User $user
     * @param $token
     * @param $host
     *
     * @return string
     */
    private function getBody(User $user, $token, $host)
    {
        $confirmationLink = $host . $this->container->get('router')->generate('confirmation', [
            'token' => $token
        ]);

        $body = 'Hi, ' . $user->getName() . '! ' .
            'It\'s confirmation message. You need to click on the link below to activate you account. ' .
            'Confirmation link : ' . $confirmationLink . ' ' .
            'Thanks. Regards, symcmf3';

        return $body;
    }

    /**
     * @param $id
     *
     * @return null|object
     */
    private function findUserActivationById($id)
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
    private function findUserActivationByToken($token)
    {
        return $this->entityManager
            ->getRepository(UserActivations::class)
            ->findOneBy(['token' => $token]);
    }

    /**
     * @return string - token
     */
    private function getToken()
    {
        return hash_hmac('sha256', random_bytes(40), $this->keyForToken);
    }

    /**
     * @param $user
     *
     * @return string
     */
    public function createConfirmation(User $user)
    {
        $activation = $this->findUserActivationById($user->getId());

        if (!$activation) {
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

        $userActivation = $this->findUserActivationById($user->id);
        if ($userActivation) {

            $userActivation->setToken($token);
            $userActivation->setUpdated(new \DateTime());

            $this->updateObject($userActivation);

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

        $userActivation = new UserActivations();
        $userActivation->setUserId($user);
        $userActivation->setToken($token);

        $this->saveObject($userActivation);

        return $token;
    }

    /**
     * @param User $user
     * @param $host
     */
    public function sendConfirmationMessage(User $user, $host)
    {
        if ($user->isActivated()) {
            return;
        }

        $token = $this->createConfirmation($user);

        if (!$token) {
            return;
        }

        $this->mailer->setMessage(
            self::CONFIRMATION_SUBJECT,
            $user->getEmail(),
            $this->getBody($user, $token, $host)
        );

        $this->mailer->send();
    }


    /**
     * Function for making user active
     *
     * @param  $token - activation link
     *
     * @return user -  objects if activation has done
     *         null -  if activation hasn't done
     */
    public function activateUser($token)
    {
        $userActivate = $this->findUserActivationByToken($token);

        if (!$userActivate) {
            return null;
        }

        $user = $this->userService->ActivatedUserById($userActivate->getUserId());
        $this->removeObject($userActivate);

        return $user;
    }
}
