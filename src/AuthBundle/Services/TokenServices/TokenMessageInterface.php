<?php

namespace AuthBundle\Services\TokenServices;

use AuthBundle\Entity\User;

/**
 * Interface TokenMessageInterface
 * @package AuthBundle\Services\TokenServices
 */
interface TokenMessageInterface
{
    /**
     * @param User $user
     * @param $token
     * @param $host
     *
     * @return string
     */
    public function getBody(User $user, $token, $host);

    /**
     * @param User $user
     * @param $host
     *
     * @return mixed
     */
    public function sendMessage(User $user, $host);

    /**
     * @return string
     */
    public function getSubject();
}
