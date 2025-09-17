<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Http\Requests\v1\ActivityPoint\GiftRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic\PointStatisticDetail as Detail;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
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
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PointStatisticController::$controllers.
 */

/**
 * Class PointStatisticController.
 * @codeCoverageIgnore
 * @ignore
 * @authenticated
 * @group activitypoint
 */
class PointStatisticController extends ApiController
{
    /**
     * @var PointStatisticRepositoryInterface
     */
    private PointStatisticRepositoryInterface $repository;

    /**
     * @var PointPackageRepositoryInterface
     */
    private PointPackageRepositoryInterface $packageRepository;

    /**
     * PointStatisticController Constructor.
     *
     * @param PointStatisticRepositoryInterface $repository
     */
    public function __construct(
        PointStatisticRepositoryInterface $repository,
        PointPackageRepositoryInterface $packageRepository,
    ) {
        $this->repository        = $repository;
        $this->packageRepository = $packageRepository;
    }

    /**
     * View item.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $context = user();

        $data    = $this->repository->viewStatistic($context, $id);

        $resource = new Detail($data);

        return $this->success($resource);
    }

    /**
     * @param  GiftRequest             $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function giftPoints(GiftRequest $request, int $id): JsonResponse
    {
        $context = user();
        $target  = UserEntity::getById($id)->detail;

        $params = $request->validated();
        $points = Arr::get($params, 'points', 0);

        ActivityPoint::giftPoints($context, $target, $points);

        return $this->success([], [], __p('activitypoint::phrase.gifted_point_to_user'));
    }
}
