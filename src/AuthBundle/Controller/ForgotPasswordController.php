<?php

namespace AuthBundle\Controller;

use AuthBundle\Form\ResetPasswordForm;
use AuthBundle\Services\TokenServices\ResetPasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ForgotPasswordController extends Controller
{
    /**
     * @return ResetPasswordService
     */
    private function getResetPasswordService()
    {
        return $this->get('auth.service.reset_password');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enterEmailAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/');
        }
        return $this->render('auth/forgot_password.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendPasswordResetEmailAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/');
        }

        $email = $request->get('_email');
        $service = $this->getResetPasswordService();

        if (!$service->sendResetMessage($email, $request->getSchemeAndHttpHost())) {
            return $this->redirectToRoute('forgot_password');
        }

        $request
            ->getSession()
            ->getFlashBag()
            ->add('success', $this->get('translator')->trans('check_email', [
                '%email%' => $email
            ]));

        return $this->redirect('/');
    }

    /**
     * @param $token
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resetPasswordAction($token, Request $request)
    {
        if (!$token) {
            return $this->redirect('/');
        }

        $service = $this->getResetPasswordService();
        $user = $service->getUserByToken($token);

        if ($user) {

            $form = $this->createForm(ResetPasswordForm::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $service->changePassword($user, $request->get('token'));

                $request
                    ->getSession()
                    ->getFlashBag()
                    ->add('success', $this->get('translator')->trans('success_reset'));

                return $this->redirect('/');
            }

            return $this->render('auth/reset_password.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $request
            ->getSession()
            ->getFlashBag()
            ->add('error', $this->get('translator')->trans('invalid_token'));

        return $this->redirect('/');
    }
}
