<?php

namespace AuthBundle\Controller;

use AppBundle\Controller\AbstractApiController;
use AuthBundle\Entity\User;
use AuthBundle\Form\FormEmail;
use AuthBundle\Form\UserType;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AuthApiController
 * @package AuthBundle\Controller
 */
class AuthApiController extends AbstractApiController
{
    /**
     * @return object
     */
    protected function getService()
    {
        return $this->get('app.service.user');
    }

    /**
     * @return string
     */
    protected function getType()
    {
        return 'user';
    }

    /**
     * Sign in
     *
     * @ApiDoc(
     *     section = "Auth",
     *     parameters = {
     *         { "name" = "email", "dataType" = "string", "required" = "true"},
     *         { "name" = "password", "dataType" = "string", "required" = "true"}
     *     },
     *     statusCodes={
     *      200="Token successfully get",
     *      401="Bad credential",
     *  }
     * )
     *
     * @Post("/login_check")
     */
    public function checkAction()
    {
        // need this fake controller
    }

    /**
     * Get current user
     *
     * @ApiDoc(
     *     section = "Auth",
     *     output={ "class"="AuthBundle\Entity\User" },
     *     statusCodes={
     *      200="Token successfully get",
     *      401="Bad credential",
     *    }
     * )
     *
     * @Get("/users/me")
     */
    public function getCurrentUserAction()
    {
        $token = $this->get('security.token_storage')->getToken();
        if (null !== $token) {
            return $token->getUser();
        }
        return $this->getUser();
    }


    /**
     * Sign out
     *
     * @ApiDoc(
     *   section = "Auth",
     *   input = {
     *      "class" = "AuthBundle\Form\UserType",
     *      "options" = {"method" = "POST", "csrf_protection" = false},
     *      "name" = ""
     *   },
     *  statusCodes={
     *      204="Registered was successfully finished",
     *      400="Returned when an error has occurred while user creation",
     *  }
     * )
     * @Post("/registration")
     *
     * @param Request $request A Symfony request
     *
     * @return Form
     *
     * @throws NotFoundHttpException
     */
    public function registrationAction(Request $request)
    {
        return parent::postEntity($request);
    }

    /**
     * Forgot password action
     *
     * @ApiDoc(
     *   section = "Auth",
     *   input = {
     *      "class" = "AuthBundle\Form\FormEmail",
     *      "options" = {"method" = "POST"},
     *      "name" = ""
     *   },
     *  statusCodes={
     *      204="Registered was successfully finished",
     *      404="Email not found ",
     *  }
     * )
     *
     * @Post("/forgot/password")
     *
     * @param Request $request A Symfony request
     *
     * @return Form
     *
     * @throws NotFoundHttpException
     */
    public function postForgotPasswordAction(Request $request)
    {
        $form = $this->createForm(FormEmail::class);

        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isValid()) {

            $email = $form->get('email')->getData();
            $result = $this
                ->get('auth.service.reset_password')
                ->sendResetMessage($email, $request->getSchemeAndHttpHost());

            if (!$result) {
                // TODO need to implement JSONApi format
                return View::create(['error' => 'email not found'], Response::HTTP_NOT_FOUND);
            }

            // nothing to return. Message was sent.
        }

        // TODO need to implement JSONApi format
        return $form;
    }

    /**
     * @param Request $request
     * @param null $id
     *
     * @return mixed
     */
    protected function handleWriteTemplate(Request $request, $id = null)
    {
        $user = $id ? $this->getService()->findById($id) : new User();

        $form = $this->createForm(UserType::class, $user, [
            'csrf_protection' => false,
        ]);

        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isValid()) {

            $user = $form->getData();

            $user = $this->getService()->addUser($user);
            $this
                ->get('auth.service.confirmation')
                ->sendMessage($user, $request->getSchemeAndHttpHost());

            // TODO need to implement JSONApi format
            return new JsonResponse([], Response::HTTP_NO_CONTENT);

        }

        // TODO need to implement JSONApi format
        return $form;
    }
}
