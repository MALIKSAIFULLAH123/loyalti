<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Http\Requests\v1\PointStatistic\Admin\AdjustRequest;
use MetaFox\ActivityPoint\Http\Requests\v1\PointStatistic\Admin\IndexRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic\Admin\PointStatisticItem;
use MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic\Admin\PointStatisticItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Repositories\PointStatisticRepositoryInterface;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PointStatisticAdminController::$controllers.
 */

/**
 * Class PointStatisticAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class PointStatisticAdminController extends ApiController
{
    /**
     * @var PointStatisticRepositoryInterface
     */
    private PointStatisticRepositoryInterface $repository;

    /**
     * PointStatisticAdminController Constructor.
     *
     * @param PointStatisticRepositoryInterface $repository
     */
    public function __construct(PointStatisticRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest            $request
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $context = user();

        $params = $request->validated();

        $data = $this->repository->viewStatistics($context, $params);

        return new ItemCollection($data);
    }

    /**
     * @throws AuthenticationException
     */
    public function adjust(AdjustRequest $request): JsonResponse
    {
        $context    = user();
        $params     = $request->validated();
        $userIds    = Arr::get($params, 'user_ids');
        $massAdjust = is_array($userIds) && count($userIds) > 1;
        $messages   = __p('activitypoint::phrase.the_points_of_this_member_have_been_adjusted_successfully');

        foreach ($userIds as $id) {
            $user = UserEntity::getById($id)->detail;

            $action = ActivityPoint::adjustPoints(user(), $user, $params['type'], $params['amount']);

            if ($action === null) {
                return $this->error(__p('activitypoint::phrase.the_point_you_want_adjust_is_over_maximum_points'));
            }

            if (!$massAdjust) {
                $result = $this->repository->find($user->entityId());

                return $this->success(new PointStatisticItem($result), [], $messages);
            }
        }

        $data = $this->repository->viewStatistics($context, $params);

        return $this->success(new ItemCollection($data), [], $messages);
    }
}
