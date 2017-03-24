<?php

namespace AuthBundle\Services\TokenServices;

use AuthBundle\Entity\User;

class ResetPasswordService extends TokenService implements TokenMessageInterface
{
    const RESET_SUBJECT = 'Reset password';

    /**
     * @param User $user
     * @param $token
     * @param $host
     *
     * @return string
     */
    public function getBody(User $user, $token, $host)
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

    /**
     * @return string
     */
    public function getSubject()
    {
        return self::RESET_SUBJECT;
    }

    /**
     * @param User $user
     * @param $host
     * @return int|void
     *
     * @throws \Exception
     */
    public function sendMessage(User $user, $host)
    {
        $token = $this->createUserToken($user);

        $user->setActivated(false);
        $this->updateObject($user);

        if (!$token) { return false; }

        $this->mailer->setMessage(
            self::RESET_SUBJECT,
            $user->getEmail(),
            $this->getBody($user, $token, $host)
        );

        return $this->mailer->send();
    }

    public function getUserByToken($token)
    {
        $userToken = $this->findUserTokenByToken($token);

        if (!$userToken) {
            return null;
        }

        return $this->userService->findUserById($userToken->getUserId());
    }

    public function changePassword($user, $token)
    {
        $this->removeUserToken($token);

        $user = $this->userService->changePassword($user);
        return $this->userService->setAuth($user);
    }

    public function sendResetMessage($email, $host)
    {
        $user = $this->userService->findUserByEmail($email);
        if (!$user) {
            $this->setFlashMessage('error', $this->container->get('translator')->trans('unknown_email'));
            return false;
        }

        return $this->sendMessage($user, $host);
    }
}
