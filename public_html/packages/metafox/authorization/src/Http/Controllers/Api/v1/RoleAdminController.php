<?php

namespace MetaFox\Authorization\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Http\Requests\v1\Role\Admin\AssignPermissionRequest;
use MetaFox\Authorization\Http\Requests\v1\Role\Admin\DeleteRequest;
use MetaFox\Authorization\Http\Requests\v1\Role\Admin\IndexRequest;
use MetaFox\Authorization\Http\Requests\v1\Role\Admin\RemovePermissionRequest;
use MetaFox\Authorization\Http\Requests\v1\Role\Admin\StoreRequest;
use MetaFox\Authorization\Http\Requests\v1\Role\Admin\UpdateRequest;
use MetaFox\Authorization\Http\Resources\v1\Role\Admin\CreateRoleForm;
use MetaFox\Authorization\Http\Resources\v1\Role\Admin\EditRoleForm;
use MetaFox\Authorization\Http\Resources\v1\Role\Admin\RoleDetail as Detail;
use MetaFox\Authorization\Http\Resources\v1\Role\Admin\RoleItemCollection as ItemCollection;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Authorization\Http\Controllers\Api\RoleAdminController::$controllers.
 */

/**
 * Class RoleAdminController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class RoleAdminController extends ApiController
{
    /**
     * @var RoleRepositoryInterface
     */
    public RoleRepositoryInterface $repository;

    /**
     * RoleAdminController constructor.
     *
     * @param RoleRepositoryInterface $repository
     */
    public function __construct(RoleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException|AuthenticationException
     * @throws AuthorizationException
     * @group admin/authorization/role
     */
    public function index(IndexRequest $request): JsonResource
    {
        $params = $request->validated();
        $data   = $this->repository->viewRoles(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @group admin/authorization/role
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createRole(user(), $params);

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url'     => '/authorization/role/browse',
                'replace' => true,
            ],
        ];

        return $this->success(new Detail($data), [
            'nextAction' => $nextAction,
        ], __p('user::phrase.created_role_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @group admin/authorization/role
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->viewRole(user(), $id);

        return $this->success(new Detail($data));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @group admin/authorization/role
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateRole(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @param AssignPermissionRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @group admin/authorization/role
     */
    public function assignPermission(AssignPermissionRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->assignRolePermission(user(), $params['role_id'], $params['permissions']);

        return $this->success(new Detail($data), [], '');
    }

    /**
     * @param RemovePermissionRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException | AuthorizationException
     * @group admin/authorization/role
     */
    public function removePermission(RemovePermissionRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->removeRolePermission(user(), $params['role_id'], $params['permissions']);

        return $this->success(new Detail($data), [], '');
    }

    /**
     * @param  DeleteRequest           $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function deleteRole(DeleteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $alternativeId = Arr::get($data, 'alternative_id');

        $deletedId = Arr::get($data, 'deleted_id');

        $deleteOption = Arr::get($data, 'delete_option');

        $context = user();

        $this->repository->deleteRole($context, $deletedId, $alternativeId, $deleteOption);

        return $this->success([
            'id' => $deletedId,
        ], [], __p('user::admin.role_successfully_deleted'));
    }

    public function create(): JsonResponse
    {
        $form = new CreateRoleForm();

        return $this->success($form);
    }

    public function edit(int $id): JsonResponse
    {
        $role = $this->repository->find($id);

        $form = new EditRoleForm($role);

        return $this->success($form);
    }
}
