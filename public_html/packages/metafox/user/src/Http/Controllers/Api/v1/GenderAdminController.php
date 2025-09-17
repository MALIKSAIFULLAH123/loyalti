<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Http\Requests\v1\UserGender\Admin\IndexRequest;
use MetaFox\User\Http\Requests\v1\UserGender\Admin\StoreRequest;
use MetaFox\User\Http\Requests\v1\UserGender\Admin\UpdateRequest;
use MetaFox\User\Http\Resources\v1\UserGender\Admin\StoreUserGenderForm;
use MetaFox\User\Http\Resources\v1\UserGender\Admin\UpdateUserGenderForm;
use MetaFox\User\Http\Resources\v1\UserGender\Admin\UserGenderItem;
use MetaFox\User\Http\Resources\v1\UserGender\Admin\UserGenderItemCollection;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\GenderAdminController::$controllers.
 */

/**
 * Class GenderAdminController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class GenderAdminController extends ApiController
{
    protected UserGenderRepositoryInterface $repository;

    /**
     * PermissionAdminController constructor.
     *
     * @param UserGenderRepositoryInterface $repository
     *
     * @group admin/user/user-gender
     */
    public function __construct(UserGenderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): UserGenderItemCollection
    {
        $context = user();

        $params = $request->validated();

        $data = $this->repository->viewGendersForAdmin($context, $params);

        return new UserGenderItemCollection($data);
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $this->repository->createGender($context, $params);

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url'     => '/user/user-gender/browse',
                'replace' => true,
            ],
        ];

        Artisan::call('cache:reset');

        return $this->success([], [
            'nextAction' => $nextAction,
        ], __p('user::phrase.gender_created_successfully'));
    }

    /**
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $gender = $this->repository->updateGender($context, $id, $params);
        Artisan::call('cache:reset');

        return $this->success(new UserGenderItem($gender), [], __p('user::phrase.gender_updated_successfully'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();
        $this->repository->deleteGender($context, $id);
        Artisan::call('cache:reset');

        return $this->success([], [], __p('user::phrase.gender_deleted_successfully'));
    }

    public function create(Request $request): JsonResponse
    {
        $form = resolve(StoreUserGenderForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }

    public function edit(int $id): UpdateUserGenderForm
    {
        $item = $this->repository->find($id);

        return new UpdateUserGenderForm($item);
    }
}
