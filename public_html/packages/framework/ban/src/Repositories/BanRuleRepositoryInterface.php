<?php

namespace MetaFox\Ban\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Ban\Models\BanRule;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface BanRule.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface BanRuleRepositoryInterface
{
    /**
     * @param  User    $context
     * @param  array   $attributes
     * @return BanRule
     */
    public function createBanRule(User $context, array $attributes): BanRule;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewBanRule(User $context, array $attributes): Paginator;

    /**
     * @param  mixed  $findValue
     * @param  string $type
     * @return bool
     */
    public function isExistBanRule(mixed $findValue, string $type): bool;

    /**
     * @param  int  $id
     * @return bool
     */
    public function deleteBanRule(int $id): bool;

    /**
     * @param  string     $type
     * @return Collection
     */
    public function getBanRulesByType(string $type): Collection;
}
