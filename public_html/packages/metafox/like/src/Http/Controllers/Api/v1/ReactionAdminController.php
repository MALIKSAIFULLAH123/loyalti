<?php

namespace MetaFox\Like\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Like\Http\Requests\v1\Reaction\Admin\DeleteRequest;
use MetaFox\Like\Http\Requests\v1\Reaction\Admin\IndexRequest;
use MetaFox\Like\Http\Requests\v1\Reaction\Admin\StoreRequest;
use MetaFox\Like\Http\Requests\v1\Reaction\Admin\UpdateRequest;
use MetaFox\Like\Http\Resources\v1\Reaction\Admin\CreateReactionForm;
use MetaFox\Like\Http\Resources\v1\Reaction\Admin\DestroyReactionForm;
use MetaFox\Like\Http\Resources\v1\Reaction\Admin\EditReactionForm;
use MetaFox\Like\Http\Resources\v1\Reaction\Admin\ReactionItemCollection as ItemCollection;
use MetaFox\Like\Repositories\ReactionAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Like\Http\Controllers\Api\ReactionAdminController::$controllers;.
 */

/**
 * Class ReactionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ReactionAdminController extends ApiController
{
    /**
     * ReactionAdminController Constructor.
     *
     * @param ReactionAdminRepositoryInterface $repository
     */
    public function __construct(protected ReactionAdminRepositoryInterface $repository)
    {
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest            $request
     * @return mixed
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewReactions(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createReaction(user(), $params);
        $this->navigate($data->admin_browse_url);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('like::phrase.reaction_was_created_successfully'));
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest           $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateReaction(user(), $id, $params);
        $this->navigate($data->admin_browse_url);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('like::phrase.reaction_was_updated_successfully'));
    }

    public function toggleActive(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        if (!$item->is_active) {
            $this->repository->checkActiveReaction();
        }

        $item->update(['is_active' => $item->is_active ? 0 : 1]);
        Artisan::call('cache:reset');

        return $this->success([], [], __p('core::phrase.already_saved_changes'));
    }

    public function create(): JsonResponse
    {
        return $this->success(new CreateReactionForm());
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        return $this->success(new EditReactionForm($item));
    }

    /**
     * @throws AuthenticationException
     */
    public function destroy(DeleteRequest $request, int $id): JsonResponse
    {
        $params        = $request->validated();
        $newReactionId = $params['new_reaction_id'];
        $this->repository->deleteReaction(user(), $id, $newReactionId);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * View deleting form.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new DestroyReactionForm($item);
        app()->call([$form, 'boot'], ['id' => $id]);

        return $this->success($form);
    }

    /**
     * @param  Request                 $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids', []);

        $this->repository->ordering(user(), $orderIds);
        Artisan::call('cache:reset');

        return $this->success([], [], __p('like::phrase.reaction_reordered_successfully'));
    }

    public function default(int $id): JsonResponse
    {
        $this->repository->getModel()->newQuery()
            ->where('is_default', 1)
            ->update([
                'is_default' => 0,
            ]);

        $item = $this->repository->find($id);
        $item->update(['is_default' => 1]);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('core::phrase.updated_successfully'));
    }
}
