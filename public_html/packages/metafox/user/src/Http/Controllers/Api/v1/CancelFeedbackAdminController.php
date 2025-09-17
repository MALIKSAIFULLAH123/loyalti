<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Http\Requests\v1\CancelFeedback\Admin\IndexRequest;
use MetaFox\User\Http\Resources\v1\CancelFeedback\Admin\CancelFeedbackDetail as Detail;
use MetaFox\User\Http\Resources\v1\CancelFeedback\Admin\CancelFeedbackItemCollection as ItemCollection;
use MetaFox\User\Http\Resources\v1\User\UserItem;
use MetaFox\User\Models\CancelFeedback;
use MetaFox\User\Repositories\CancelFeedbackAdminRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\CancelFeedbackAdminController::$controllers.
 */

/**
 * Class CancelFeedbackAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class CancelFeedbackAdminController extends ApiController
{
    /**
     * @var CancelFeedbackAdminRepositoryInterface
     */
    public CancelFeedbackAdminRepositoryInterface $repository;

    /**
     * CancelFeedbackAdminController constructor.
     *
     * @param CancelFeedbackAdminRepositoryInterface $repository
     */
    public function __construct(CancelFeedbackAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection<UserItem>
     * @group admin/user/feedback
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();
        $params  = $request->validated();
        $query   = $this->repository->viewFeedbacks($context, $params);

        $limit = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $data  = $query->paginate($limit);

        return new ItemCollection($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/user/feedback
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var CancelFeedback $item */
        $item =  $this->repository->find($id);

        $item->delete();

        return $this->success([
            'id' => $id,
        ], [], __p('user::phrase.feedback_deleted_successfully'));
    }
}
