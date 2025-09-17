<?php

namespace MetaFox\Layout\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Layout\Repositories\ThemeRepositoryInterface;
use MetaFox\Layout\Models\Theme;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ThemeRepository.
 */
class ThemeRepository extends AbstractRepository implements ThemeRepositoryInterface
{
    public function model()
    {
        return Theme::class;
    }

    public function getActiveThemeIds(string $resolution = 'web'): array
    {
        return Theme::query()
            ->where('resolution', $resolution)
            ->where('is_active', 1)
            ->pluck('theme_id')
            ->toArray();
    }
}
