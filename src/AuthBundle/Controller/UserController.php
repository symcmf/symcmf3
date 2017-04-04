<?php

namespace AuthBundle\Controller;

use AppBundle\Controller\AbstractApiController;
use AuthBundle\Entity\Role;
use AuthBundle\Entity\User;
use AuthBundle\Entity\UserRole;
use AuthBundle\Form\UserType;
use AuthBundle\Repository\UserRoleRepository;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use NilPortugues\Symfony\JsonApiBundle\Serializer\JsonApiResponseTrait;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class UserController extends AbstractApiController
{
    use JsonApiResponseTrait;

    /**
     * @return \AuthBundle\Services\UserService
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
     * @param User $user
     *
     * @return Response
     */
    protected function getJsonApiResponse(User $user)
    {
        $serializer = $this->get('nil_portugues.serializer.json_api_serializer');

        /** @var \NilPortugues\Api\JsonApi\JsonApiTransformer $transformer */
        $transformer = $serializer->getTransformer();
        $transformer->setSelfUrl($this->generateUrl('get_users', ['id' => $user->getId()], true));

        return $this->response($serializer->serialize($user));
    }

    /**
     * @ApiDoc(
     *  description="This is a description of your API method",
     *  section="User"
     * )
     * List all users.
     *
     * @Get("/api/users")
     *
     * @QueryParam(name="_page", requirements="\d+", default=1, nullable=true, description="Page number.")
     * @QueryParam(name="_perPage", requirements="\d+", default=30, nullable=true, description="Limit.")
     * @QueryParam(name="_sortField", nullable=true, description="Sort field.")
     * @QueryParam(name="_sortDir", nullable=true, description="Sort direction.")
     *
     * @param Request $request the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getUsersAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        return parent::getList($paramFetcher);
    }

    /**
     * Retrieves a specific user.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="user id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when user is not found"
     *  },
     *  section="User"
     * )
     *
     * @Get("/api/users/{id}")
     *
     * @param $id
     *
     * @return User
     *
     * @throws NotFoundHttpException
     */
    public function getUserAction($id)
    {
        return parent::getEntity($id);
    }

    /**
     * Adds a user.
     *
     * @ApiDoc(
     *   input = {
     *      "class" = "AuthBundle\Form\UserType",
     *      "options" = {"method" = "POST", "csrf_protection" = false},
     *      "name" = ""
     *   },
     *  output={ "class"="AuthBundle\Entity\User" },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while user creation",
     *  },
     *  section="User"
     * )
     *
     * @Post(
     *     "/api/users",
     *      options={"csrf_protection" = false}
     * )
     *
     * @param Request $request A Symfony request
     *
     * @return User|Form
     *
     * @throws NotFoundHttpException
     */
    public function postUserAction(Request $request)
    {
        return parent::postEntity($request);
    }


    /**
     * Updates an user
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="user id"},
     *  },
     *  input = {
     *      "class" = "AuthBundle\Form\UserType",
     *      "options" = {"method" = "PUT", "csrf_protection" = false},
     *      "name" = ""
     *   },
     *  output={ "class"="AuthBundle\Entity\User" },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while updating the user",
     *      404="Returned when unable to find the message template"
     *  },
     *  section="User"
     * )
     *
     * @Put(
     *     "/api/users/{id}",
     *      options={"csrf_protection" = false}
     * )
     *
     * @param int $id a user template identifier
     * @param Request $request A Symfony request
     *
     * @return User
     *
     * @throws NotFoundHttpException
     */
    public function putUserAction(Request $request, $id)
    {
        return parent::putEntity($request, $id);
    }

    /**
     * Deletes a user
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="user id"}
     *  },
     *  statusCodes={
     *      200="Returned when user is successfully deleted",
     *      400="Returned when an error has occurred while user deletion",
     *      404="Returned when unable to find user"
     *  },
     *  section="User"
     * )
     *
     * @Delete("/api/users/{id}")
     *
     * @param int $id A user identifier
     *
     * @return View|JsonResponse
     *
     * @throws NotFoundHttpException
     */
    public function deleteUserAction($id)
    {
        return parent::deleteEntity($id);
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

        $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);

        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isValid()) {
            return $this->getService()->addUser($user);
        }

        return $form;
    }

    /**
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="user id"}
     *  },
     *  description="This is a description of your API method",
     *  section="User"
     * )
     * List all user roles.
     *
     * @Get("/api/users/{id}/roles")
     *
     * @QueryParam(name="_page", requirements="\d+", default=1, nullable=true, description="Page number.")
     * @QueryParam(name="_perPage", requirements="\d+", default=30, nullable=true, description="Limit.")
     * @QueryParam(name="_sortField", nullable=true, description="Sort field.")
     * @QueryParam(name="_sortDir", nullable=true, description="Sort direction.")
     *
     * @param $id
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getUserRolesAction($id, ParamFetcherInterface $paramFetcher)
    {
        /** @var User $user*/
        $user = $this->getService()->findById($id);

        $manyToMany = [
            'classFrom' => 'AuthBundle\Entity\UserRole',
            'classJoin' => 'AuthBundle\Entity\Role',
            'classFromField' => 'role',
            'classJoinField' => 'id',
            'fieldForWhere' => 'user'
        ];

        return parent::getChildList($paramFetcher, 'users', $user->getId(), $manyToMany);
    }

    /**
     * Retrieves a specific role.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="user id"},
     *      {"name"="rid", "dataType"="integer", "requirement"="\d+", "description"="role id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when article is not found"
     *  },
     *  section="User"
     * )
     *
     * @Get("/api/users/{id}/roles/{rid}")
     *
     * @param $id
     * @param $rid
     *
     * @return Role
     *
     * @throws NotFoundHttpException
     */
    public function getCategoryArticleAction($id, $rid)
    {
        $role = $this->getService()->getUserRoleById($id, $rid);

        if (!$role) {
            throw new NotFoundHttpException(sprintf('User role (%d) not found', $rid));
        }

        return $role;
    }

    /**
     * Adds a user.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="user id"},
     *      {"name"="rid", "dataType"="integer", "requirement"="\d+", "description"="role id"}
     *  },
     *  output={ "class"="AuthBundle\Entity\User" },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while user creation",
     *  },
     *  section="User"
     * )
     *
     * @Post(
     *     "/api/users/{id}/roles",
     *      options={"csrf_protection" = false}
     * )
     *
     * @param $id
     * @param Request $request A Symfony request
     *
     * @return User|Form
     *
     * @throws NotFoundHttpException
     */
    public function postUserRoleAction($id, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user =  $this->getEntityManager()->getRepository(User::class)->find($id);
        /** @var Role $role */
        $role =  $this->getEntityManager()->getRepository(Role::class)->find($data['rid']);
        /** @var UserRoleRepository $userRoleRepository */
        $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);
        $usersRoles = $userRoleRepository->getUserRoleByUserIdAndRoleId($id, $data['rid']);

        if (!$usersRoles) {
            $userRole = new UserRole();
            $userRole->setUser($user);
            $userRole->setRole($role);
            $user->addRole($userRole);
            $this->getEntityManager()->persist($userRole);
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Delete user role
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="user id"},
     *      {"name"="rid", "dataType"="integer", "requirement"="\d+", "description"="role id"}
     *  },
     *  statusCodes={
     *      200="Returned when category is successfully deleted",
     *      400="Returned when an error has occurred while article deletion",
     *      404="Returned when unable to find category"
     *  },
     *  section="User"
     * )
     *
     * @Delete("/api/users/{id}/roles/{rid}")
     *
     * @param int $id
     * @param int $rid
     *
     * @return View|JsonResponse
     *
     * @throws NotFoundHttpException
     */
    public function deleteUserRoleAction($id, $rid)
    {
        /** @var User $user */
        $user =  $this->getEntityManager()->getRepository(User::class)->find($id);
        /** @var Role $role */
        $role = $this->getService()->getUserRoleById($id, $rid);

        if (!$user|| !$role) {
            throw new NotFoundHttpException('User or role not found');
        }

        try {
            /** @var UserRoleRepository $userRoleRepository */
            $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);
            $usersRoles = $userRoleRepository->getUserRoleByUserIdAndRoleId($id, $rid);

            foreach ($usersRoles as $userRole) {
                $this->getEntityManager()->remove($userRole);
            }
            $this->getEntityManager()->flush();

        } catch (\Exception $e) {
            return View::create(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
