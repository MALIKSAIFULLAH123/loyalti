<?php

namespace MetaFox\Page\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Page.
 * @mixin BaseRepository
 * @method Page getModel()
 * @method Page find($id, $columns = ['*'])
 *
 * @mixin CollectTotalItemStatTrait
 */
interface PageAdminRepositoryInterface extends HasSponsor
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @throws AuthorizationException
     */
    public function viewPages(User $context, array $attributes): Builder;

    /**
     * @param  array      $pageIds
     * @return Collection
     */
    public function getPagesByPageIds(array $pageIds): Collection;

    /**
     * @param  User    $context
     * @param  int     $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deletePage(User $context, int $id): bool;
}
