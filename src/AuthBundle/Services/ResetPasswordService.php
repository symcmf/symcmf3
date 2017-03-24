<?php

namespace AuthBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\UserActivations;
use AppBundle\Services\AbstractService;
use AppBundle\Services\UserService;
use Doctrine\ORM\EntityManager;
use MessageBundle\Services\Mailers\MailerService;

class ResetPasswordService extends AbstractService
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var ActivationService
     */
    protected $activationService;

    protected $mailer;

    private $keyForToken;

    const RESET_SUBJECT = 'Reset password';

    public function __construct(EntityManager $entityManager, $container, UserService $userService, ActivationService $activationService, MailerService $mailerService, $keyForToken)
    {
        parent::__construct($entityManager, $container);
        $this->userService = $userService;
        $this->activationService = $activationService;
        $this->mailer = $mailerService;
        $this->keyForToken = $keyForToken;
    }

    /**
     * @return string - token
     */
    private function getToken()
    {
        return hash_hmac('sha256', random_bytes(40), $this->keyForToken);
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
     * @param $token
     * @param $host
     *
     * @return string
     */
    private function getBody(User $user, $token, $host)
    {
        $resetLink = $host . $this->container->get('router')->generate('reset_password', [
                'token' => $token
            ]);

        $body = 'Hi, ' . $user->getName() . '! ' .
            'It\'s reset password message. You need to click on the link below to reset you password. ' .
            'Link : ' . $resetLink . ' ' .
            'Thanks. Regards, symcmf3';

        return $body;
    }

    public function getUserByToken($token)
    {
        $userActivate = $this->activationService->findUserActivationByToken($token);

        if (!$userActivate) {
            return null;
        }

        return $this->userService->findUserById($userActivate->getUserId());
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
        $userActivate = $this->activationService->findUserActivationByToken($token);

        if (!$userActivate) {
            return null;
        }

        $user = $this->userService->activatedUserById($userActivate->getUserId());
        $this->removeObject($userActivate);

        return $user;
    }

    public function changePassword($user)
    {
        $userActivate = $this->activationService->findUserActivationById($user->getId());

        if (!$userActivate) {
            return null;
        }

        $this->userService->activatedUserById($userActivate->getUserId());
        $this->removeObject($userActivate);

        $user = $this->userService->changePassword($user);
        return $this->userService->setAuth($user);
    }

    public function sendResetMessage($email, $host)
    {
        $user = $this->userService->findUserByEmail($email);
        if (!$user) {
            throw new \Exception('User with entered email not found');
        }

        $token = $this->createToken($user);
        $user->setActivated(false);
        $this->updateObject($user);

        if (!$token) {
            return;
        }

        $this->mailer->setMessage(
            self::RESET_SUBJECT,
            $user->getEmail(),
            $this->getBody($user, $token, $host)
        );

        $this->mailer->send();
    }
}
