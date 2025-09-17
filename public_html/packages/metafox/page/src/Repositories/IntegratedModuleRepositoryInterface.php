<?php

namespace MetaFox\Page\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface IntegratedModule.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface IntegratedModuleRepositoryInterface
{
    /**
     * @param int $pageId
     * @return void
     */
    public function addModules(int $pageId): void;

    /**
     * @param int $pageId
     * @return Collection
     */
    public function getModules(int $pageId): Collection;

    /**
     * @param User  $context
     * @param int   $pageId
     * @param array $params
     * @return bool
     */
    public function updateModule(User $context, int $pageId, array $params): bool;

    /**
     * @param User  $context
     * @param array $attributes
     * @return bool
     */
    public function orderModules(User $context, array $attributes): bool;

    /**
     * @param int $pageId
     * @return array
     */
    public function getProfileMenuSettings(int $pageId): array;

    /**
     * @param int $pageId
     * @return array
     */
    public function getMenusByPage(int $pageId): array;
}
