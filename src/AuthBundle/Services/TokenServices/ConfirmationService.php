<?php

namespace AuthBundle\Services\TokenServices;

use AuthBundle\Entity\User;

/**
 * Class ConfirmationService
 * @package AuthBundle\Services\TokenServices
 */
class ConfirmationService extends TokenService  implements TokenMessageInterface
{

    const CONFIRMATION_SUBJECT = 'Confirmation message';

    /**
     * @param User $user
     * @param $token
     * @param $host
     *
     * @return string
     */
    public function getBody(User $user, $token, $host)
    {
        $confirmationLink = $host . $this->router->generate('confirmation', [
                'token' => $token
            ]);

        $body = 'Hi, ' . $user->getUsername() . '! ' .
            'It\'s confirmation message. You need to click on the link below to activate you account. ' .
            'Confirmation link : ' . $confirmationLink . ' ' .
            'Thanks. Regards, symcmf3';

        return $body;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return self::CONFIRMATION_SUBJECT;
    }

    /**
     * @param User $user
     * @param $host
     *
     * @return mixed
     */
    public function sendMessage(User $user, $host)
    {
        if ($user->isActivated()) { return false; }

        $token = $this->createUserToken($user);

        if (!$token) { return false; }

        $this->mailer->setMessage(
            $this->getSubject(),
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
        return $this->removeUserToken($token);
    }
}
