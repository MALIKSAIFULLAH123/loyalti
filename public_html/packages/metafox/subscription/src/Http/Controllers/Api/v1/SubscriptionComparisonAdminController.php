<?php

namespace MetaFox\Subscription\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionComparison\Admin\IndexRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionComparison\Admin\StoreRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionComparison\Admin\UpdateRequest;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\CreateSubscriptionComparisonForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\EditSubscriptionComparisonForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\SubscriptionComparisonDetail as Detail;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Subscription\Http\Controllers\Api\SubscriptionComparisonAdminController::$controllers.
 */

/**
 * Class SubscriptionComparisonAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionComparisonAdminController extends ApiController
{
    /**
     * @var SubscriptionComparisonRepositoryInterface
     */
    private SubscriptionComparisonRepositoryInterface $repository;

    /**
     * SubscriptionComparisonAdminController Constructor.
     *
     * @param SubscriptionComparisonRepositoryInterface $repository
     */
    public function __construct(SubscriptionComparisonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $context = user();

        $params = $request->validated();

        $data = $this->repository->viewComparisons($context, $params);

        $meta = [];

        if (!$data->count()) {
            Arr::set($meta, 'empty_message', __p('subscription::admin.no_comparison_features_have_been_added'));
        }

        return $this->success($data, $meta);
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->createComparison($context, $params);

        $resource = new Detail($data);

        $response = $resource->toArray($request);

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url'     => '/subscription/comparison/browse',
                'replace' => true,
            ],

        ];

        return $this->success(
            $response,
            [
                'nextAction' => $nextAction,
            ],
            __p('subscription::admin.comparison_feature_successfully_created')
        );
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
        $params = $request->validated();

        $context = user();

        $data = $this->repository->updateComparison($context, $id, $params);

        $resource = new Detail($data);

        $response = $resource->toArray($request);

        return $this->success($response, [], __p('subscription::admin.comparison_feature_successfully_updated'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();

        $this->repository->deleteComparison($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('subscription::admin.comparison_successfully_deleted'));
    }

    public function create(Request $request): JsonResponse
    {
        $form = resolve(CreateSubscriptionComparisonForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters);
        }

        return $this->success($form);
    }

    public function edit(Request $request)
    {
        $form = resolve(EditSubscriptionComparisonForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters);
        }

        return $this->success($form);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids', []);

        $this->repository->ordering(user(), $orderIds);

        return $this->success([], [], __p('like::phrase.reaction_reordered_successfully'));
    }
}
