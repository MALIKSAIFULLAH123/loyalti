<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Group\Models\ExampleRule;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ExampleRuleRepositoryInterface.
 * @mixin BaseRepository
 * @method ExampleRule find($id, $columns = ['*'])
 * @method ExampleRule getModel()
 */
interface ExampleRuleRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewRuleExamples(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return ExampleRule
     * @throws AuthorizationException
     */
    public function createRuleExample(User $context, array $attributes): ExampleRule;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return ExampleRule
     * @throws AuthorizationException
     */
    public function updateRuleExample(User $context, int $id, array $attributes): ExampleRule;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteRuleExample(User $context, int $id): bool;

    /**
     * @param User            $context
     * @param array<int, int> $orders
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function orderRuleExamples(User $context, array $orders): bool;

    /**
     * @param User $context
     * @param int  $id
     * @param int  $isActive
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function updateActive(User $context, int $id, int $isActive): bool;

    /**
     * @param User $context
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function getAllActiveRuleExamples(User $context): Collection;

    /**
     * @param User $context
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function getAllActiveRuleExsForForm(User $context): array;
}
