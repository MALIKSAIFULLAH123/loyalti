<?php

namespace MetaFox\Story\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Story\Http\Requests\v1\BackgroundSet\Admin\DeleteRequest;
use MetaFox\Story\Http\Requests\v1\BackgroundSet\Admin\IndexRequest;
use MetaFox\Story\Http\Requests\v1\BackgroundSet\Admin\StoreRequest;
use MetaFox\Story\Http\Requests\v1\BackgroundSet\Admin\UpdateRequest;
use MetaFox\Story\Http\Resources\v1\BackgroundSet\Admin\BackgroundSetItemCollection as ItemCollection;
use MetaFox\Story\Http\Resources\v1\BackgroundSet\Admin\StoreBackgroundSetForm;
use MetaFox\Story\Http\Resources\v1\BackgroundSet\Admin\UpdateBackgroundSetForm;
use MetaFox\Story\Http\Resources\v1\BackgroundSet\BackgroundSetDetail as Detail;
use MetaFox\Story\Repositories\BackgroundSetRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Story\Http\Controllers\Api\BackgroundSetAdminController::$controllers;.
 */

/**
 * Class BackgroundSetAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class BackgroundSetAdminController extends ApiController
{
    /**
     * BackgroundSetAdminController Constructor.
     *
     * @param BackgroundSetRepositoryInterface $repository
     */
    public function __construct(protected BackgroundSetRepositoryInterface $repository)
    {
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewBackgroundSets(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createBackgroundSet(user(), $params);
        $this->navigate($data->admin_browse_url);

        return $this->success(new Detail($data), [], __p('story::phrase.created_successfully'));
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateBackgroundSet(user(), $id, $params);
        $this->navigate($data->admin_browse_url);

        return $this->success(new Detail($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteBackgroundSet(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('story::phrase.deleted_successfully'));
    }

    public function toggleActive(int $id): JsonResponse
    {
        $itemActive = $this->repository->getBackgroundSetActive();

        if ($itemActive->entityId() == $id) {
            abort(422, __p('story::validation.there_must_be_at_least_one_wallpaper_set_activated'));
        }

        $item = $this->repository->find($id);

        $item->update(['is_active' => $item->is_active ? 0 : 1]);
        $itemActive->update(['is_active' => $itemActive->is_active ? 0 : 1]);

        return $this->success([], [], __p('core::phrase.already_saved_changes')
        );
    }

    /**
     * @return StoreBackgroundSetForm
     */
    public function create(): StoreBackgroundSetForm
    {
        return new StoreBackgroundSetForm();
    }

    /**
     * @param int $id
     * @return UpdateBackgroundSetForm
     */
    public function edit(int $id): UpdateBackgroundSetForm
    {
        $item = $this->repository->find($id);

        return new UpdateBackgroundSetForm($item);
    }

    /**
     * @param DeleteRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchDelete(DeleteRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);
        if (empty($ids)) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'));
        }

        $itemActive = $this->repository->getBackgroundSetActive();
        if (in_array($itemActive->entityId(), $ids)) {
            abort(403, json_encode([
                'title'   => __p('core::phrase.oops'),
                'message' => __p('story::phrase.the_activated_collection_cannot_be_deleted'),
            ]));
        }

        foreach ($ids as $id) {
            $this->repository->deleteBackgroundSet(user(), $id);
        }

        return $this->success([], [], __p('story::phrase.deleted_successfully'));
    }
}
