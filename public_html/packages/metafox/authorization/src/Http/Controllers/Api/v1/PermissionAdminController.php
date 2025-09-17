<?php

namespace MetaFox\Authorization\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Http\Requests\v1\Permission\Admin\EditFormRequest;
use MetaFox\Authorization\Http\Resources\v1\Permission\Admin\SearchPermissionForm;
use MetaFox\Authorization\Http\Resources\v1\Role\Admin\RoleItemCollection;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\PermissionAdminController::$controllers.
 */

/**
 * Class PermissionAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class PermissionAdminController extends ApiController
{
    /**
     * @var PermissionRepositoryInterface
     */
    protected $perms;

    /**
     * @var RoleRepositoryInterface
     */
    protected $roles;

    /**
     * PermissionAdminController constructor.
     *
     * @param PermissionRepositoryInterface $repository
     * @param RoleRepositoryInterface       $roles
     *
     * @group admin/authorization/permission
     */
    public function __construct(PermissionRepositoryInterface $repository, RoleRepositoryInterface $roles)
    {
        $this->perms = $repository;
        $this->roles = $roles;
    }

    public function index()
    {
        return $this->success(new RoleItemCollection($this->roles->all()));
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @group admin/authorization/permission
     * @throws AuthenticationException
     */
    public function update(Request $request, int $id): JsonResponse
    {

        $role     = $this->roles->find($id);
        $context  = user();
        $moduleId = $request->get('module_id', 'user');
        $values   = Arr::dot($request->all());

        if (!in_array($moduleId, app('core.packages')->getActivePackageAliases())) {
            return $this->error('Package not found');
        }

        [, $driver, ,] = app('core.drivers')->loadDriver(Constants::DRIVER_TYPE_FORM, 'user.edit_permission', 'admin');

        try {
            [, $driver, ,] = app('core.drivers')->loadDriver(
                Constants::DRIVER_TYPE_FORM,
                "$moduleId.edit_permission",
                'admin'
            );
        } catch (\Exception $e) {
        }

        $form = resolve($driver);
        if (method_exists($form, 'validated')) {
            $values = app()->call([$form, 'validated'], $request->route()->parameters());
        }



        // Unset these param because user may search for setting before send form to api
        // And module_id, role_id is also included

        Arr::forget($values, ['role_id', 'module_id']);

        $this->perms->updatePermissionValue($context, $role, $values);

        return $this->success([], [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @param EditFormRequest $request
     *
     * @return AbstractForm
     * @group admin/authorization/permission
     */
    public function edit(EditFormRequest $request): AbstractForm
    {
        $params   = $request->validated();
        $moduleId = Arr::get($params, 'module_id');

        if (!in_array($moduleId, app('core.packages')->getActivePackageAliases())) {
            throw new RecordsNotFoundException();
        }

        [, $driver, ,] = app('core.drivers')->loadDriver(Constants::DRIVER_TYPE_FORM, 'user.edit_permission', 'admin');
        try {
            [, $driver, ,] = app('core.drivers')->loadDriver(
                Constants::DRIVER_TYPE_FORM,
                "$moduleId.edit_permission",
                'admin'
            );
        } catch (\Exception $e) {
        }

        $form = resolve($driver);
        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $form;
    }

    /**
     * @return AbstractForm
     * @group admin/authorization/permission
     */
    public function searchForm(): AbstractForm
    {
        return new SearchPermissionForm(null);
    }
}
