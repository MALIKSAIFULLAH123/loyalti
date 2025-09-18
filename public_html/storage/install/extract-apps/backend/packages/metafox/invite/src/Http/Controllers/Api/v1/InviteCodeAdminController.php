<?php

namespace MetaFox\Invite\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Invite\Http\Requests\v1\InviteCode\Admin\BatchRefreshRequest;
use MetaFox\Invite\Http\Requests\v1\InviteCode\Admin\IndexRequest;
use MetaFox\Invite\Http\Resources\v1\InviteCode\Admin\InviteCodeItemCollection as ItemCollection;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Invite\Http\Controllers\Api\InviteCodeAdminController::$controllers;
 */

/**
 * Class InviteCodeAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class InviteCodeAdminController extends ApiController
{
    /**
     * @var InviteCodeRepositoryInterface
     */
    private InviteCodeRepositoryInterface $repository;

    /**
     * InviteCodeAdminController Constructor
     *
     * @param InviteCodeRepositoryInterface $repository
     */
    public function __construct(InviteCodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $limit  = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $data   = $this->repository->viewUserCodes($params)->paginate($limit, ['invite_codes.*']);

        return new ItemCollection($data);
    }

    public function refresh(int $id): JsonResponse
    {
        $model = $this->repository->find($id);

        $model->update([
            'code' => $this->repository->generateUniqueCodeValue(),
        ]);

        return $this->success([], [], __p('invite::phrase.refresh_invite_code_successfully'));
    }

    public function batchRefresh(BatchRefreshRequest $request): JsonResponse
    {
        $params = $request->validated();

        foreach ($params['id'] as $id) {
            $model = $this->repository->find($id);

            $model?->updateQuietly([
                'code' => $this->repository->generateUniqueCodeValue(),
            ]);
        }

        return $this->success([], [], __p('invite::phrase.refresh_invite_code_successfully'));
    }
}
