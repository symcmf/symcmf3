<?php

namespace AuthBundle\Controller;

use AuthBundle\Services\SocialService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSocialController extends Controller
{
    /**
     * @var string
     */
    protected $clientType = 'facebook_main'; // default value

    /**
     * @var SocialService
     */
    protected $socialService;

    /**
     * Link to this controller to start the "connect" process
     */
    public function connectAction()
    {
        return $this->get('oauth2.registry')
            ->getClient($this->clientType)
            ->redirect();
    }

    /**
     * Social redirect redirects to back here afterwards
     */
    public function connectCheckAction(Request $request)
    {
        $this->socialService = $this->getSocialService();

        $client = $this->get('oauth2.registry')
            ->getClient($this->clientType);

        $socialUser = $client->fetchUser();

        if (!$this->socialService->auth($socialUser)) {
//            TODO flash message with error if not log
        }

        return $this->redirect('/');
    }

    /**
     * @return SocialService
     */
    protected abstract function getSocialService();

}
