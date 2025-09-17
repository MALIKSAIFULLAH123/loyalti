<?php

namespace MetaFox\Layout\Repositories;

use MetaFox\Layout\Models\Snippet;
use MetaFox\User\Models\User;
use Prettus\Repository\Eloquent\BaseRepository;
use ZipArchive;

/**
 * Interface Snippet.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SnippetRepositoryInterface
{
    /**
     * @param  User   $user
     * @param  string $theme
     * @param  string $variant
     * @param  array  $data
     * @param  bool   $active
     * @return mixed
     */
    public function saveTheme(
        User $user,
        string $theme,
        string $variant,
        array $data,
        bool $active
    );

    /**
     * @param  User   $user
     * @param  string $theme
     * @param  string $variant
     * @param  array  $data
     * @param  bool   $active
     * @return mixed
     */
    public function saveVariant(
        User $user,
        string $theme,
        string $variant,
        array $data,
        bool $active
    );

    /**
     * Purge all temporary snippets.
     *
     * @return void
     */
    public function purge(): void;

    /**
     * @param  \ArrayObject $data
     * @param  ZipArchive   $zip
     * @return void
     */
    public function attachBuildArchive(\ArrayObject $data, ZipArchive $zip, ?string $reason=null): void;
}
