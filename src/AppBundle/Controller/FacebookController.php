<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class FacebookController extends Controller
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/facebook")
     */
    public function connectAction()
    {
        return $this->get('oauth2.registry')
            ->getClient('facebook_main')
            ->redirect();
    }

    /**
     * Facebook redirects to back here afterwards
     *
     * @Route("/connect/facebook/check", name="connect_facebook_check")
     */
    public function connectCheckAction(Request $request)
    {
        $client = $this->get('oauth2.registry')
            ->getClient('facebook_main');

        $facebookUser = $client->fetchUser();

        $user = new User();

        $user->setEmail($facebookUser->getEmail());
        $user->setName($facebookUser->getFirstName() . '' . $facebookUser->getLastName());
        // Encode the new users password
        $encoder = $this->get('security.password_encoder');
        $password = $encoder->encodePassword($user, $facebookUser->getId());
        $user->setPassword($password);

        // Set their role
        $user->setRole('ROLE_USER');

        // Save
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

//        die('ddd');

        $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_secured_area', serialize($token));

        return $this->redirect('/');

        // do something with all this new power!
//        var_dump($user); die();

    }
}