<?php

namespace MetaFox\Invite\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Invite\Http\Requests\v1\Invite\Admin\BatchDeletedRequest;
use MetaFox\Invite\Http\Requests\v1\Invite\Admin\IndexRequest;
use MetaFox\Invite\Http\Resources\v1\Invite\Admin\InviteItemCollection as ItemCollection;
use MetaFox\Invite\Repositories\InviteAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Invite\Http\Controllers\Api\InviteAdminController::$controllers;
 */

/**
 * Class InviteAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class InviteAdminController extends ApiController
{
    /**
     * InviteAdminController Constructor
     *
     * @param InviteAdminRepositoryInterface $repository
     */
    public function __construct(
        protected InviteAdminRepositoryInterface $repository
    ) {}

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewInvites(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Delete item
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteInvite(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('invite::phrase.deleted_invited_successfully'));
    }

    public function batchDelete(BatchDeletedRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        foreach ($ids as $id) {
            $this->repository->deleteInvite(user(), $id);
        }

        return $this->success([], [], __p('invite::phrase.delete_invites_successfully'));
    }
}
