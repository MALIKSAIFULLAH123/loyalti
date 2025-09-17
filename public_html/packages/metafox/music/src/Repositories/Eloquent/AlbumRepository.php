<?php

namespace MetaFox\Music\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Album as Model;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Policies\AlbumPolicy;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Music\Repositories\GenreDataRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Music\Support\Browse\Scopes\Album\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Album\ViewScope;
use MetaFox\Music\Support\Browse\Scopes\Genre\GenreScope;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class AlbumPrivacyStreamRepository.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class AlbumRepository extends AbstractRepository implements AlbumRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasSponsorInFeed;
    use UserMorphTrait;
    use CollectTotalItemStatTrait;

    public function model()
    {
        return Album::class;
    }

    private function attachmentRepository(): AttachmentRepositoryInterface
    {
        return resolve(AttachmentRepositoryInterface::class);
    }

    private function songRepository(): SongRepositoryInterface
    {
        return resolve(SongRepositoryInterface::class);
    }

    private function genreRepository(): GenreDataRepositoryInterface
    {
        return resolve(GenreDataRepositoryInterface::class);
    }

    public function viewAlbums(ContractUser $context, ContractUser $owner, array $attributes): Paginator
    {
        $limit = $attributes['limit'];
        $view  = $attributes['view'];

        $this->withUserMorphTypeActiveScope();

        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature($limit);
        }

        $query = $this->buildQueryViewAlbums($context, $owner, $attributes);

        return $query
            ->with(['userEntity', 'ownerEntity', 'albumText', 'attachments'])
            ->simplePaginate($limit, ['music_albums.*']);
    }

    private function buildQueryViewAlbums(User $context, User $owner, array $attributes): Builder
    {
        $sort       = Arr::get($attributes, 'sort', Browse::SORT_RECENT);
        $sortType   = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_DESC);
        $when       = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $view       = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $search     = Arr::get($attributes, 'q');
        $genreId    = Arr::get($attributes, 'genre_id');
        $profileId  = $attributes['user_id'] ?? 0;
        $isFeatured = Arr::get($attributes, 'is_featured');

        if (!$context->isGuest()) {
            if ($profileId == $context->entityId()) {
                $view = Browse::VIEW_MY;
            }
        }

        /**
         * @var PrivacyScope $privacyScope
         */
        $privacyScope = resolve(PrivacyScope::class)
            ->setUserId($context->entityId())
            ->setModerationPermissionName('music_album.moderate')
            ->setHasUserBlock(true);

        /**
         * @var SortScope $sortScope
         */
        $sortScope = resolve(SortScope::class)
            ->setSort($sort)
            ->setSortType($sortType);

        /**
         * @var WhenScope $whenScope
         */
        $whenScope = resolve(WhenScope::class)
            ->setWhen($when);

        /**
         * @var ViewScope $viewScope
         */
        $viewScope = resolve(ViewScope::class)
            ->setUserContext($context)
            ->setView($view);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query->addScope(resolve(SearchScope::class, ['query' => $search, 'fields' => ['name']]));
        }

        if ($genreId) {
            $query->addScope(resolve(GenreScope::class, [
                'itemType' => Album::ENTITY_TYPE,
                'genreId'  => $genreId,
            ]));
        }

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());
            $viewScope->setIsViewOwner(true);
        }

        $query->addScope(new FeaturedScope($isFeatured));

        $query = $this->applyDisplayAlbumSetting($query, $owner, $view);

        if (!$isFeatured) {
            $query->addScope($privacyScope);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }

    /**
     * @param Builder $query
     * @param User    $owner
     * @param string  $view
     *
     * @return Builder
     */
    private function applyDisplayAlbumSetting(Builder $query, User $owner, string $view): Builder
    {
        if ($view == Browse::VIEW_MY) {
            return $query;
        }

        /*
         * Does not support view pending items from Group in My Pending Photos
         */
        if (!$owner instanceof HasPrivacyMember) {
            $query->where('music_albums.owner_type', '=', $owner->entityType());
        }

        return $query;
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_featured', HasFeature::IS_FEATURED)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_sponsor', \MetaFox\Platform\Contracts\HasSponsor::IS_SPONSOR)
            ->where('is_approved', '=', 1)
            ->simplePaginate($limit);
    }

    /**
     * @param ContractUser $context
     * @param ContractUser $owner
     * @param array        $attributes
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function createAlbum(ContractUser $context, ContractUser $owner, array $attributes): Model
    {
        policy_authorize(AlbumPolicy::class, 'create', $context, $owner);
        app('flood')->checkFloodControlWhenCreateItem($context, Model::ENTITY_TYPE);

        $songs = Arr::get($attributes, 'songs', []);

        app('quota')->checkQuotaControlWhenCreateItem(
            $context,
            Model::ENTITY_TYPE,
            1,
            ['message' => __p('music::web.you_have_reached_your_limit', ['entity_type' => Model::ENTITY_TYPE])]
        );

        if (Arr::has($attributes, 'description')) {
            $description = Arr::get($attributes, 'description');

            if (null === $description) {
                $description = MetaFoxConstant::EMPTY_STRING;
            }

            $attributes['description'] = $description;
        }

        $thumbTempFile = Arr::get($attributes, 'thumb_temp_file', 0);

        if ($thumbTempFile > 0) {
            $thumbnailTemp               = upload()->getFile($thumbTempFile);
            $attributes['image_file_id'] = $thumbnailTemp->entityId();
        }

        $attributes['name'] = $this->cleanTitle($attributes['name']);

        $statistics = $this->handleAlbumStatistics($context, $owner, $songs);

        $attributes = array_merge($attributes, [
            'user_id'        => $context->entityId(),
            'user_type'      => $context->entityType(),
            'owner_id'       => $owner->entityId(),
            'owner_type'     => $owner->entityType(),
            'total_track'    => $statistics['total_track'] ?? 0,
            'total_duration' => $statistics['total_duration'] ?? 0,
        ]);

        $model = $this->getModel()->newModelInstance();

        $model->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $model->setPrivacyListAttribute($attributes['list']);
        }

        $model->save();

        if (!empty($attributes['attachments'])) {
            $this->attachmentRepository()->updateItemId($attributes['attachments'], $model);
        }

        $model->refresh();

        $this->genreRepository()->updateData($model, $attributes['genres']);

        $this->handleSongFile($context, $owner, $model->entityId(), $songs, $attributes);

        if ($thumbTempFile > 0) {
            // Delete temp file after done
            upload()->rollUp($attributes['image_file_id']);
        }

        return $model;
    }

    private function handleAlbumStatistics(ContractUser $context, ContractUser $owner, array $songs): array
    {
        $totalSong     = 0;
        $totalDuration = 0;

        $autoApproved = $context->hasPermissionTo('music_song.auto_approved');

        /*
         * TODO: improve this for next version because we does not have song_set to group songs into a feed
         */
        if ($owner->hasPendingMode()) {
            $autoApproved = true;
        }

        $keptStatus = [MetaFoxConstant::FILE_UPDATE_STATUS, MetaFoxConstant::FILE_REMOVE_STATUS];

        $newSongs = array_filter($songs, function ($song) {
            return $song['status'] == MetaFoxConstant::FILE_NEW_STATUS;
        });

        $keptSongs = array_filter($songs, function ($song) use ($keptStatus) {
            return in_array($song['status'], $keptStatus) && $song['is_approved'] == 1;
        });

        if ($autoApproved) {
            $totalSong     += count($newSongs);
            $totalDuration += $this->calcDurationSong($newSongs);
        }

        $totalSong     += count($keptSongs);
        $totalDuration += $this->calcDurationSong($keptSongs);

        return [
            'total_track'    => $totalSong,
            'total_duration' => $totalDuration,
        ];
    }

    private function calcDurationSong($songs): int
    {
        $duration = 0;

        foreach ($songs as $song) {
            if (isset($song['duration'])) {
                $duration += $song['duration'];
                continue;
            }

            $duration += $this->songRepository()->getSongDuration($song['temp_file']);
        }

        return $duration;
    }

    /**
     * @param ContractUser $context
     * @param ContractUser $owner
     * @param int          $albumId
     * @param array        $songs
     * @param array        $attributes
     *
     * @return void
     * @throws \Exception
     */
    private function handleSongFile(ContractUser $context, ContractUser $owner, int $albumId, array $songs, array $attributes = []): void
    {
        if (empty($songs)) {
            return;
        }

        foreach ($songs as $song) {
            $song = $this->transformSongAttributes($albumId, $song, $attributes);

            $this->songRepository()->createSong($context, $owner, $song);
        }
    }

    private function transformSongAttributes(int $albumId, array $song, array $attributes = []): array
    {
        return array_merge([
            'album_id'    => $albumId,
            'name'        => Arr::get($song, 'name'),
            'description' => Arr::get($song, 'description'),
            'privacy'     => Arr::get($attributes, 'privacy'),
            'list'        => Arr::get($attributes, 'list'),
        ], $song);
    }

    public function updateAlbum(ContractUser $context, int $id, array $attributes): Model
    {
        $removeThumbnail = Arr::get($attributes, 'remove_thumbnail', 0);

        $thumbTempFile = Arr::get($attributes, 'thumb_temp_file', 0);

        $album = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(AlbumPolicy::class, 'update', $context, $album);

        if (Arr::has($attributes, 'privacy') && !$context->can('updatePrivacy', [$album, $attributes['privacy']])) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
        }

        if (isset($attributes['name'])) {
            $attributes['name'] = $this->cleanTitle($attributes['name']);
        }

        if ($removeThumbnail) {
            $oldFile = $album->image_file_id;
            app('storage')->deleteFile($oldFile, null);
            $attributes['image_file_id'] = null;
        }

        if ($thumbTempFile > 0) {
            $tempFile = upload()->getFile($thumbTempFile);

            $attributes['image_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($thumbTempFile);
        }

        $album->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $album->setPrivacyListAttribute($attributes['list']);
        }

        $album->save();

        $this->genreRepository()->updateData($album, $attributes['genres']);

        if (!empty($attributes['attachments'])) {
            $this->attachmentRepository()->updateItemId($attributes['attachments'], $album);
        }

        if (Arr::has($attributes, 'songs')) {
            $this->handleUpdateSongAlbum($context, $context, $album, $attributes);
        }

        $album->refresh();

        if ($thumbTempFile > 0 || $removeThumbnail) {
            // Delete temp file after done
            if ($album->image_file_id) {
                upload()->rollUp($album->image_file_id);
                app('storage')->deleteFile($album->image_file_id, null);
            }
        }

        return $album;
    }

    /**
     * @param ContractUser $context
     * @param ContractUser $owner
     * @param int          $albumId
     * @param array        $attributes
     *
     * @return void
     * @throws \Exception
     */
    private function handleUpdateSongAlbum(
        ContractUser $context,
        ContractUser $owner,
        Album        $album,
        array        &$attributes
    ): void {

        $fileSongs   = collect(Arr::get($attributes, 'songs', []));
        $newItems    = $fileSongs->get(MetaFoxConstant::FILE_NEW_STATUS, []);
        $updateItems = $fileSongs->get(MetaFoxConstant::FILE_UPDATE_STATUS, []);
        $removeItems = $fileSongs->get(MetaFoxConstant::FILE_REMOVE_STATUS, []);

        unset($attributes['songs']);

        /** @var Album $album */
        $songs      = [...$newItems, ...$updateItems, ...$removeItems];
        $statistics = $this->handleAlbumStatistics($context, $owner, $songs);

        $album->update($statistics);

        if (!empty($newItems)) {
            $this->handleSongFile($context, $owner, $album->entityId(), $newItems, $attributes);
        }

        if (!empty($updateItems)) {
            $this->syncItemsWithAlbum($context, $updateItems, $album->entityId());
        }

        if (!empty($removeItems)) {
            $this->removeAlbumItems($removeItems);
        }
    }

    /**
     * @param ContractUser $context
     * @param array        $songs
     * @param int          $albumId
     *
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    protected function syncItemsWithAlbum(ContractUser $context, array $songs, int $albumId): void
    {
        foreach ($songs as $song) {
            $this->songRepository()->updateSong($context, $song['id'], $song);
        }
    }

    private function removeAlbumItems(array $songs)
    {
        foreach ($songs as $song) {
            $this->songRepository()->find($song['id'])->delete();
        }
    }

    public function viewAlbum(ContractUser $context, int $id): Model
    {
        $album = $this
            ->withUserMorphTypeActiveScope()
            ->with(['user', 'userEntity', 'attachments'])
            ->find($id);

        policy_authorize(AlbumPolicy::class, 'view', $context, $album);

        $album->incrementTotalView();

        return $album->refresh();
    }

    public function viewAlbumItems(User $context, int $id, array $attributes = []): Paginator
    {
        /**
         * @var Model $album
         */
        $album = $this->getModel()->query()->findOrFail($id);

        $limit = !empty($attributes['limit']) ? $attributes['limit'] : Pagination::DEFAULT_ITEM_PER_PAGE;

        $query = $this->buildQueryAlbumItems($context, $album, $attributes);

        return $query
            ->simplePaginate($limit);
    }

    public function deleteAlbum(ContractUser $context, int $id): bool
    {
        $album = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(AlbumPolicy::class, 'delete', $context, $album);

        $album->delete();

        return true;
    }

    /**
     * @param User                 $context
     * @param Model                $album
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function buildQueryAlbumItems(User $context, Model $album, array $attributes): Builder
    {
        $sort = $attributes['sort'] ?? Browse::SORT_RECENT;

        $sortType = $attributes['sort_type'] ?? Browse::SORT_TYPE_ASC;

        $query = Song::query();

        $sortScope = new SortScope();

        $sortScope->setSort($sort)->setSortType($sortType);

        $query->where('music_songs.album_id', '=', $album->entityId());

        $buildApproved = true;

        if ($context->hasPermissionTo('music_song.approve')) {
            $buildApproved = false;
        }

        if ($context->entityId() == $album->userId()) {
            $buildApproved = false;
        }

        if ($buildApproved) {
            $query->where('music_songs.is_approved', '=', 1);
        }

        return $query
            ->addScope($sortScope);
    }
}
