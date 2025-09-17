<?php

namespace MetaFox\StaticPage\Repositories;

use MetaFox\StaticPage\Models\StaticPage;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StaticPageContent.
 *
 * @mixin BaseRepository
 */
interface StaticPageContentRepositoryInterface
{
    /**
     * @param  StaticPage           $staticPage
     * @param  array<string, mixed> $attributes
     * @return null|bool
     */
    public function updateOrCreateContent(StaticPage $staticPage, array $attributes): ?bool;
}
