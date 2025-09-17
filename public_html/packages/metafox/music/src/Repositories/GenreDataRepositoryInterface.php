<?php

namespace MetaFox\Music\Repositories;

use MetaFox\Platform\Contracts\Content;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface GenreDataRepositoryInterface.
 * @mixin BaseRepository
 */
interface GenreDataRepositoryInterface
{
    /**
     * @param  Content $content
     * @param  ?array  $genreIds
     * @return void
     */
    public function updateData(Content $content, ?array $genreIds = []): void;

    /**
     * @param  Content $content
     * @param  array   $genreIds
     * @return void
     */
    public function deleteData(Content $content, array $genreIds = []): void;
}
