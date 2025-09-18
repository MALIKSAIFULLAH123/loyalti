<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Group\Http\Requests\v1\ExampleRule\Admin\IndexRequest;
use MetaFox\Group\Http\Requests\v1\ExampleRule\Admin\StoreRequest;
use MetaFox\Group\Http\Requests\v1\ExampleRule\Admin\UpdateRequest;
use MetaFox\Group\Http\Resources\v1\ExampleRule\Admin\ExampleRuleItem;
use MetaFox\Group\Http\Resources\v1\ExampleRule\Admin\ExampleRuleItemCollection;
use MetaFox\Group\Http\Resources\v1\ExampleRule\Admin\StoreExampleRuleForm;
use MetaFox\Group\Http\Resources\v1\ExampleRule\Admin\UpdateExampleRuleForm;
use MetaFox\Group\Repositories\ExampleRuleRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Platform\Http\Requests\v1\OrderingRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\ExampleRuleAdminController::$controllers.
 */

/**
 * Class ExampleRuleAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class ExampleRuleAdminController extends ApiController
{
    /**
     * @var ExampleRuleRepositoryInterface
     */
    private ExampleRuleRepositoryInterface $repository;

    /**
     * ExampleRuleAdminController constructor.
     *
     * @param ExampleRuleRepositoryInterface $repository
     */
    public function __construct(ExampleRuleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse example rules.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewRuleExamples(user(), $params);

        return new ExampleRuleItemCollection($data);
    }

    /**
     * Create a new example rule.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createRuleExample(user(), $params);

        Artisan::call('cache:reset');
        return $this->success(new ExampleRuleItem($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/group/example-rule/browse',
                ],
            ],
        ], __p('group::phrase.successfully_created_example_group_rule'));
    }

    /**
     * Update an example rule.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateRuleExample(user(), $id, $params);

        Artisan::call('cache:reset');
        return $this->success(
            new ExampleRuleItem($data),
            [],
            __p('group::phrase.successfully_updated_example_group_rule')
        );
    }

    /**
     * Remove an example rule.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteRuleExample(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('group::phrase.successfully_deleted_example_group_rule'));
    }

    /**
     * View creation form.
     *
     * @return StoreExampleRuleForm
     */
    public function create(): StoreExampleRuleForm
    {
        return new StoreExampleRuleForm();
    }

    /**
     * View editing form.
     *
     * @param int $id
     * @return UpdateExampleRuleForm
     */
    public function edit(int $id): UpdateExampleRuleForm
    {
        $item = $this->repository->find($id);

        return new UpdateExampleRuleForm($item);
    }

    /**
     * Reorder example rule.
     *
     * @param OrderingRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids', []);
        $this->repository->orderRuleExamples(user(), $orderIds);

        return $this->success([], [], __p('group::phrase.rule_reordered_successfully'));
    }

    /**
     * Active example rule.
     *
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $this->repository->updateActive(user(), $id, $params['active']);

        return $this->success([
            'id'        => $id,
            'is_active' => (int) $params['active'],
        ], [], __p('core::phrase.already_saved_changes'));
    }
}
