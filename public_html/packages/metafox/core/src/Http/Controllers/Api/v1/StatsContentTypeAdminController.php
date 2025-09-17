<?php

namespace MetaFox\Core\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Core\Http\Resources\v1\StatsContentType\Admin\StatsContentTypeItemCollection as ItemCollection;
use MetaFox\Core\Http\Resources\v1\StatsContentType\Admin\StatsContentTypeDetail as Detail;
use MetaFox\Core\Repositories\StatsContentTypeAdminRepositoryInterface;
use MetaFox\Core\Http\Requests\v1\StatsContentType\Admin\IndexRequest;
use MetaFox\Core\Http\Requests\v1\StatsContentType\Admin\UpdateRequest;
use MetaFox\Core\Http\Resources\v1\StatsContentType\Admin\EditStatsContentTypeForm;
use MetaFox\Platform\Http\Requests\v1\OrderingRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class StatsContentTypeAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class StatsContentTypeAdminController extends ApiController
{
    /**
     * @var StatsContentTypeAdminRepositoryInterface
     */
    private StatsContentTypeAdminRepositoryInterface $repository;

    /**
     * StatsContentTypeAdminController Constructor.
     *
     * @param StatsContentTypeAdminRepositoryInterface $repository
     */
    public function __construct(StatsContentTypeAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewTypes($context, $params);

        return new ItemCollection($data);
    }

    public function edit(Request $request, int $id): JsonResponse
    {
        $form = app()->make(EditStatsContentTypeForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], array_merge($request->route()->parameters(), ['id' => $id]));
        }

        return $this->success($form);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->updateType($context, $id, $params);

        return $this->success(new Detail($data), [], __p('core::phrase.update_stats_type_successfully'));
    }

    /**
     * For ordering the content type.
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids', []);

        if (!is_array($orderIds)) {
            return $this->success([]);
        }

        $result = $this->repository->orderTypes($orderIds);
        if (!$result) {
            return $this->error(__p('core::phrase.cannot_order_stats_types_now'));
        }

        return $this->success([], [], __p('core::phrase.order_stats_type_successfully'));
    }
}
