<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Http\Requests\v1\Rule\CreateFormRequest;
use MetaFox\Group\Http\Requests\v1\Rule\IndexRequest;
use MetaFox\Group\Http\Requests\v1\Rule\OrderingRequest;
use MetaFox\Group\Http\Requests\v1\Rule\StoreRequest;
use MetaFox\Group\Http\Requests\v1\Rule\UpdateRequest;
use MetaFox\Group\Http\Resources\v1\Rule\RuleItem;
use MetaFox\Group\Http\Resources\v1\Rule\RuleItemCollection as ItemCollection;
use MetaFox\Group\Http\Resources\v1\Rule\StoreGroupRuleForm;
use MetaFox\Group\Http\Resources\v1\Rule\UpdateGroupRuleForm;
use MetaFox\Group\Models\Rule;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\RuleRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\RuleController::$controllers.
 */

/**
 * Class RuleController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class RuleController extends ApiController
{
    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $repository;
    /**
     * @var GroupRepositoryInterface
     */
    private GroupRepositoryInterface $groupRepository;

    /**
     * RuleController constructor.
     *
     * @param RuleRepositoryInterface  $repository
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(RuleRepositoryInterface $repository, GroupRepositoryInterface $groupRepository)
    {
        $this->repository      = $repository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Browse group rules.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewRules(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Create group rule.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $rule   = $this->repository->createRule(user(), $params);

        return $this->success(new RuleItem($rule), [], __p('group::phrase.a_group_rule_has_been_created'));
    }

    /**
     * Update a group rule.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateRule(user(), $id, $params);

        return $this->success(new RuleItem($data), [], __p('group::phrase.successfully_updated_group_rule'));
    }

    /**
     * Remove a rule.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteRule(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('group::phrase.a_group_rule_has_been_deleted'));
    }

    /**
     * Reorder rules.
     *
     * @param OrderingRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function orderRules(OrderingRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->orderRules(user(), $params);

        return $this->success([], [], __p('group::phrase.group_rules_successfully_ordered'));
    }

    /**
     * View creation form.
     *
     * @param CreateFormRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function createForm(CreateFormRequest $request): JsonResponse
    {
        $rule    = new Rule();
        $context = user();

        $data  = $request->validated();
        $group = $this->groupRepository->find($data['group_id']);

        policy_authorize(GroupPolicy::class, 'update', $context, $group);
        $rule->group_id = $group->entityId();

        return $this->success(new StoreGroupRuleForm($rule), [], '');
    }

    /**
     * View update form.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function editForm(int $id): JsonResponse
    {
        $rule    = $this->repository->with(['group'])->find($id);
        $context = user();
        policy_authorize(GroupPolicy::class, 'update', $context, $rule->group);

        return $this->success(new UpdateGroupRuleForm($rule), [], '');
    }
}
