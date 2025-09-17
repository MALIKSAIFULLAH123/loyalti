<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\ExampleRule;
use MetaFox\Group\Policies\ExampleRulePolicy;
use MetaFox\Group\Repositories\ExampleRuleRepositoryInterface;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class ExampleRuleRepository.
 * @method ExampleRule find($id, $columns = ['*'])
 * @method ExampleRule getModel()
 * @ignore
 */
class ExampleRuleRepository extends AbstractRepository implements ExampleRuleRepositoryInterface
{
    public function model(): string
    {
        return ExampleRule::class;
    }

    public function getPhraseRepository(): PhraseRepositoryInterface
    {
        return resolve(PhraseRepositoryInterface::class);
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function createRuleExample(User $context, array $attributes): ExampleRule
    {
        policy_authorize(ExampleRulePolicy::class, 'create', $context);

        $exampleRule = new ExampleRule();
        $exampleRule->fill($attributes);
        $exampleRule->save();

        return $exampleRule->refresh();
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function updateRuleExample(User $context, int $id, array $attributes): ExampleRule
    {
        policy_authorize(ExampleRulePolicy::class, 'update', $context);

        $rule = $this->getModel()->newModelQuery()->find($id);

        $rule->fill($attributes);
        $rule->save();

        return $rule->refresh();
    }

    public function viewRuleExamples(User $context, array $attributes): Paginator
    {
        policy_authorize(ExampleRulePolicy::class, 'viewAny', $context);
        $limit = $attributes['limit'];

        return $this->getModel()->newQuery()
            ->orderBy('ordering')
            ->simplePaginate($limit);
    }

    public function deleteRuleExample(User $context, int $id): bool
    {
        policy_authorize(ExampleRulePolicy::class, 'delete', $context);
        $rule = $this->find($id);
        if (!$rule instanceof ExampleRule) {
            abort(401, __p('phrase.permission_deny'));
        }

        $this->getPhraseRepository()->deleteWhere(['key' => $rule->title]);
        $this->getPhraseRepository()->deleteWhere(['key' => $rule->description]);

        return (bool) $rule->delete();
    }

    public function orderRuleExamples(User $context, array $orders): bool
    {
        policy_authorize(ExampleRulePolicy::class, 'update', $context);

        foreach ($orders as $id => $order) {
            ExampleRule::query()->where('id', $id)->update(['ordering' => $order]);
        }

        return true;
    }

    public function updateActive(User $context, int $id, int $isActive): bool
    {
        policy_authorize(ExampleRulePolicy::class, 'update', $context);
        $rule = $this->find($id);

        return $rule->update(['is_active' => $isActive]);
    }

    public function getAllActiveRuleExamples(User $context): Collection
    {
        policy_authorize(ExampleRulePolicy::class, 'viewAny', $context);

        return $this->getModel()->newQuery()
            ->where('is_active', '=', ExampleRule::IS_ACTIVE)
            ->orderBy('ordering')
            ->get();
    }

    public function getAllActiveRuleExsForForm(User $context): array
    {
        return $this->getAllActiveRuleExamples($context)->map(function (ExampleRule $ruleExample) {
            return [
                'title'       => __p($ruleExample->title),
                'description' => __p($ruleExample->description),
            ];
        })->toArray();
    }
}
