<?php

namespace MetaFox\Layout\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Theme.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ThemeRepositoryInterface
{
    /**
     * Get activated theme IDs.
     * @param string $resolution
     *
     * @return array<string>
     */
    public function getActiveThemeIds(string $resolution = 'web'): array;
}
