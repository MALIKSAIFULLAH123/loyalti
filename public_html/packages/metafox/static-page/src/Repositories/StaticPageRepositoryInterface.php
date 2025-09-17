<?php

namespace MetaFox\StaticPage\Repositories;

use MetaFox\StaticPage\Models\StaticPage;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StaticPage.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface StaticPageRepositoryInterface
{
    /**
     * @param  array<string, mixed> $attributes
     * @return StaticPage
     */
    public function createStaticPage(array $attributes): StaticPage;

    /**
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return StaticPage
     */
    public function updateStaticPage(int $id, array $attributes): StaticPage;

    /**
     * @param  int  $id
     * @return bool
     */
    public function deleteStaticPage(int $id): bool;

    /**
     * @return array
     */
    public function getStaticPageOptions(): array;

    /**
     * @param  null|int $id
     * @return string
     */
    public function getStaticPageUrlById(?int $id): string;
}
