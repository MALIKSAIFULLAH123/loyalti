<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Group\Models\Rule;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\RuleRepositoryInterface;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class RuleRepository.
 * @method Rule find($id, $columns = ['*'])
 * @method Rule getModel()
 * @ignore
 */
class RuleRepository extends AbstractRepository implements RuleRepositoryInterface
{
    public function model(): string
    {
        return Rule::class;
    }

    /**
     * @return GroupRepositoryInterface
     */
    private function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewRules(User $context, array $attributes): Paginator
    {
        $groupId = $attributes['group_id'];
        $group   = $this->groupRepository()->find($groupId);
        policy_authorize(GroupPolicy::class, 'view', $context, $group);

        $limit = $attributes['limit'];

        return $this->getModel()->newQuery()
            ->where('group_id', $groupId)
            ->orderBy('ordering')
            ->simplePaginate($limit);
    }

    /**
     * @param int $groupId
     *
     * @return Collection|null
     */
    public function getRulesForForm(int $groupId): ?Collection
    {
        return $this->getModel()->newQuery()
            ->where('group_id', $groupId)
            ->orderBy('ordering')
            ->get();
    }

    public function createRule(User $context, array $attributes): Rule
    {
        $groupId        = $attributes['group_id'];
        $maxRule        = GroupFacade::getMaximumNumberGroupRule();
        $totalGroupRule = 0;
        $groupRule      = $this->getRulesForForm($groupId);
        if ($groupRule !== null) {
            $totalGroupRule = $groupRule->count();
        }

        if ($totalGroupRule >= $maxRule) {
            abort(403, __p('group::phrase.maximum_number_group_rule_message'));
        }

        $group = $this->groupRepository()->find($groupId);
        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $group);

        $attributes['ordering'] = $this->getNextOrdering($groupId);

        $rule = new Rule($attributes);
        $rule->save();

        $rule->group->incrementAmount('total_rule');

        return $rule->refresh();
    }

    protected function getNextOrdering(int $groupId): int
    {
        $currentRule = $this->getModel()
            ->newQuery()
            ->where([
                'group_id' => $groupId,
            ])
            ->orderByDesc('ordering')
            ->first();

        if (!$currentRule instanceof Rule) {
            return 0;
        }

        return (int) $currentRule->ordering + 1;
    }

    public function updateRule(User $context, int $id, array $attributes): Rule
    {
        $rule = $this->with(['group'])->find($id);
        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $rule->group);

        $rule->update($attributes);

        return $rule;
    }

    public function deleteRule(User $context, int $id): bool
    {
        $rule = $this->with(['group'])->find($id);
        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $rule->group);

        $rule->group->decrementAmount('total_rule');

        return (bool) $rule->delete();
    }

    public function orderRules(User $context, array $attributes): bool
    {
        $groupId = $attributes['group_id'];
        $group   = $this->groupRepository()->find($groupId);
        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        $orderIds = $attributes['orders'];
        $rules    = $this->getModel()->newQuery()
            ->where('group_id', $groupId)
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        if (!$rules->count()) {
            return true;
        }

        $ordering = 1;

        foreach ($orderIds as $orderId) {
            $rule = $rules->get($orderId);

            if (!is_object($rule)) {
                continue;
            }

            $rule->update(['ordering' => $ordering++]);
        }

        return true;
    }
}
