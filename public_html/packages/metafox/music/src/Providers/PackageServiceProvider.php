<?php

namespace MetaFox\Music\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Music\Contracts\SupportInterface;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\AlbumText;
use MetaFox\Music\Models\Genre;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Observers\AlbumObserver;
use MetaFox\Music\Observers\PlaylistObserver;
use MetaFox\Music\Observers\SongObserver;
use MetaFox\Music\Repositories\AlbumAdminRepositoryInterface;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Music\Repositories\Eloquent\AlbumAdminRepository;
use MetaFox\Music\Repositories\Eloquent\AlbumRepository;
use MetaFox\Music\Repositories\Eloquent\GenreDataRepository;
use MetaFox\Music\Repositories\Eloquent\GenreRepository;
use MetaFox\Music\Repositories\Eloquent\PlaylistAdminRepository;
use MetaFox\Music\Repositories\Eloquent\PlaylistDataRepository;
use MetaFox\Music\Repositories\Eloquent\PlaylistRepository;
use MetaFox\Music\Repositories\Eloquent\SongAdminRepository;
use MetaFox\Music\Repositories\Eloquent\SongRepository;
use MetaFox\Music\Repositories\GenreDataRepositoryInterface;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Music\Repositories\PlaylistAdminRepositoryInterface;
use MetaFox\Music\Repositories\PlaylistDataRepositoryInterface;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Music\Repositories\SongAdminRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Music\Support\Support;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class PackageServiceProvider.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        SongRepositoryInterface::class          => SongRepository::class,
        AlbumRepositoryInterface::class         => AlbumRepository::class,
        PlaylistRepositoryInterface::class      => PlaylistRepository::class,
        SongAdminRepositoryInterface::class     => SongAdminRepository::class,
        AlbumAdminRepositoryInterface::class    => AlbumAdminRepository::class,
        PlaylistAdminRepositoryInterface::class => PlaylistAdminRepository::class,
        PlaylistDataRepositoryInterface::class  => PlaylistDataRepository::class,
        GenreRepositoryInterface::class         => GenreRepository::class,
        SupportInterface::class                 => Support::class,
        GenreDataRepositoryInterface::class     => GenreDataRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Song::ENTITY_TYPE     => Song::class,
            Album::ENTITY_TYPE    => Album::class,
            Playlist::ENTITY_TYPE => Playlist::class,
            Genre::ENTITY_TYPE    => Genre::class,
        ]);

        Song::observe([EloquentModelObserver::class, SongObserver::class]);
        Album::observe([EloquentModelObserver::class, AlbumObserver::class]);
        AlbumText::observe([EloquentModelObserver::class]);
        Playlist::observe([EloquentModelObserver::class, PlaylistObserver::class]);
        Genre::observe([EloquentModelObserver::class]);
    }
}
