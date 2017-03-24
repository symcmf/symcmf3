<?php

namespace AuthBundle\Services\SocialServices;

use AuthBundle\Entity\User;

/**
 * Class FacebookService
 * @package AuthBundle\Services\SocialServices
 */
class FacebookService extends SocialService
{
    /**
     * @param $socialId
     *
     * @return null|object
     */
    protected function findBySocialId($socialId)
    {
        return $this->userService->findUserByFacebookId($socialId);
    }

    /**
     * @param $socialId
     * @param User $user
     *
     * @return User
     */
    protected function setSocialId($socialId, User $user)
    {
        return $user->setFacebookId($socialId);
    }
}
