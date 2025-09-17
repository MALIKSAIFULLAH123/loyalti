<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Http\Requests\v1\UserRelation\Admin\IndexRequest;
use MetaFox\User\Http\Requests\v1\UserRelation\Admin\StoreRequest;
use MetaFox\User\Http\Requests\v1\UserRelation\Admin\UpdateRequest;
use MetaFox\User\Http\Resources\v1\UserRelation\Admin\CreateForm;
use MetaFox\User\Http\Resources\v1\UserRelation\Admin\EditForm;
use MetaFox\User\Http\Resources\v1\UserRelation\Admin\UserRelationDetail as Detail;
use MetaFox\User\Http\Resources\v1\UserRelation\Admin\UserRelationItem as Item;
use MetaFox\User\Http\Resources\v1\UserRelation\Admin\UserRelationItemCollection as ItemCollection;
use MetaFox\User\Repositories\UserRelationRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\UserRelationAdminController::$controllers.
 */

/**
 * Class UserRelationAdminController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class UserRelationAdminController extends ApiController
{
    /**
     * @var UserRelationRepositoryInterface
     */
    public UserRelationRepositoryInterface $repository;

    /**
     * UserRelationAdminController constructor.
     *
     * @param UserRelationRepositoryInterface $repository
     */
    public function __construct(UserRelationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse relation.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection<Item>
     * @group admin/user/relation
     * @authenticated
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewRelationShips(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Create user relation.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group admin/user/relation
     * @authenticated
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createRelationShip(user(), $params);
        $this->navigate($data->admin_browse_url);
        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('user::phrase.relationship_has_been_created_successfully'));
    }

    /**
     * Update user relationship.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group admin/user/relation
     * @authenticated
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        Arr::set($params, 'id', $id);
        $data = $this->repository->updateRelationShip(user(), $params);
        $this->navigate($data->admin_browse_url);
        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('user::phrase.relationship_has_been_updated_successfully'));
    }

    /**
     * Delete user relationship`.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group admin/user/relation
     * @authenticated
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteRelation(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('user::phrase.relation_was_deleted_successfully'));
    }

    /**
     * View editing form.
     *
     * @param int $id
     *
     * @return AbstractForm
     * @group admin/user/relation
     * @authenticated
     */
    public function edit(int $id): AbstractForm
    {
        return new EditForm($this->repository->find($id));
    }

    /**
     * View creation form.
     *
     * @return AbstractForm
     * @group admin/user/relation
     * @authenticated
     */
    public function create(): AbstractForm
    {
        return new CreateForm();
    }

    /**
     * Update active status.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function toggleActive(int $id): JsonResponse
    {
        $item = $this->repository->activeRelation($id);

        return $this->success([new Detail($item)], [], __p('core::phrase.already_saved_changes'));
    }
}
