<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Group\Models\Rule;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface RuleRepositoryInterface.
 * @mixin BaseRepository
 * @method Rule find($id, $columns = ['*'])
 * @method Rule getModel()
 */
interface RuleRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewRules(User $context, array $attributes): Paginator;

    /**
     * @param int $groupId
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function getRulesForForm(int $groupId): ?Collection;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Rule
     * @throws AuthorizationException
     */
    public function createRule(User $context, array $attributes): Rule;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Rule
     * @throws AuthorizationException
     */
    public function updateRule(User $context, int $id, array $attributes): Rule;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteRule(User $context, int $id): bool;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function orderRules(User $context, array $attributes): bool;
}
