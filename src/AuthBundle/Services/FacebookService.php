<?php

namespace AuthBundle\Services;

use AppBundle\Entity\User;

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
