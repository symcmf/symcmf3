<?php

namespace AuthBundle\Controller;

use AuthBundle\Form\UserResetPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ForgotPasswordController extends Controller
{
    public function enterEmailAction()
    {
        // TODO check auth or not
        return $this->render(
            'auth/forgotPassword.html.twig');
    }

    public function sendPasswordResetEmailAction(Request $request)
    {
        // TODO add validation
        $email = $request->get('_email');

        $service = $this->get('auth.service.reset_password')->sendResetMessage($email, $request->getSchemeAndHttpHost());

        // TODO add message about correct sending message
        return $this->redirect('/');
    }

    public function resetPasswordAction($token, Request $request)
    {
        $user = $this->get('auth.service.reset_password')->getUserByToken($token);

        if ($user) {

            $form = $this->createForm(UserResetPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $this->get('auth.service.reset_password')->changePassword($user);
                return $this->redirect('/');
            }

            return $this->render('auth/resetPassword.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // TODO add error message about incorrect token
        return $this->redirect('/');
    }
}
