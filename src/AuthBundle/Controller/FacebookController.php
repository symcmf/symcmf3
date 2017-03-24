<?php

namespace AuthBundle\Controller;

class FacebookController extends AbstractSocialController
{
    /**
     * @return object
     */
    protected function getSocialService()
    {
        return $this->get('auth.service.social.facebook');
    }
}
