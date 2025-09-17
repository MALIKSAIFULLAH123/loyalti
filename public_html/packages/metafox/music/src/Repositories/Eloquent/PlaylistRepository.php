<?php

namespace MetaFox\Music\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Playlist as Model;
use MetaFox\Music\Models\PlaylistData;
use MetaFox\Music\Policies\PlaylistPolicy;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Music\Support\Browse\Scopes\Playlist\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Playlist\ViewScope;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Traits\UserMorphTrait;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;

/**
 * Class PlaylistRepository.
 * @method Model find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class PlaylistRepository extends AbstractRepository implements PlaylistRepositoryInterface
{
    use UserMorphTrait;
    use CollectTotalItemStatTrait;

    public function model()
    {
        return Playlist::class;
    }

    private function attachmentRepository(): AttachmentRepositoryInterface
    {
        return resolve(AttachmentRepositoryInterface::class);
    }

    public function viewPlaylists(ContractUser $context, ContractUser $owner, array $attributes): Paginator
    {
        $limit = $attributes['limit'];
        $view  = $attributes['view'];

        $this->withUserMorphTypeActiveScope();

        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature(6);
        }

        $query = $this->buildQueryViewPlaylists($context, $owner, $attributes);

        return $query
            ->with(['userEntity', 'ownerEntity', 'isFavorite'])
            ->simplePaginate($limit, ['music_playlists.*']);
    }

    private function buildQueryViewPlaylists(ContractUser $context, ContractUser $owner, array $attributes): Builder
    {
        $sort      = Arr::get($attributes, 'sort', Browse::SORT_RECENT);
        $sortType  = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_DESC);
        $when      = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $view      = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $search    = Arr::get($attributes, 'q');
        $profileId = $attributes['user_id'] ?? 0;

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
            ->setModerationPermissionName('music_playlist.moderate')
            ->setHasUserBlock(true);

        /**
         * @var SortScope $sortScope
         */
        $sortScope = resolve(SortScope::class)
            ->setSort($sort)->setSortType($sortType);

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

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());
            $viewScope->setIsViewOwner(true);
        }

        $query = $this->applyDisplayPlaylistSetting($query, $owner, $view);

        return $query
            ->addScope($privacyScope)
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }

    private function applyDisplayPlaylistSetting(Builder $query, ContractUser $owner, string $view): Builder
    {
        if ($view == Browse::VIEW_MY) {
            return $query;
        }

        /*
         * Does not support view pending items from Group in My Pending Photos
         */
        if (!$owner instanceof HasPrivacyMember) {
            $query->where('music_playlists.owner_type', '=', $owner->entityType());
        }

        return $query;
    }

    /**
     * @param  ContractUser           $context
     * @param  ContractUser           $owner
     * @param  array                  $attributes
     * @return Model
     * @throws AuthorizationException
     */
    public function createPlaylist(ContractUser $context, ContractUser $owner, array $attributes): Model
    {
        policy_authorize(PlaylistPolicy::class, 'create', $context, $owner);
        app('flood')->checkFloodControlWhenCreateItem($context, Playlist::ENTITY_TYPE);
        app('quota')->checkQuotaControlWhenCreateItem(
            $context,
            Model::ENTITY_TYPE,
            1,
            ['message' => __p('music::web.you_have_reached_your_limit', ['entity_type' => Model::ENTITY_TYPE])]
        );

        if (null === Arr::get($attributes, 'description')) {
            Arr::set($attributes, 'description', MetaFoxConstant::EMPTY_STRING);
        }

        $thumbTempFile = Arr::get($attributes, 'thumb_temp_file', 0);

        if ($thumbTempFile > 0) {
            $thumbnailTemp               = upload()->getFile($thumbTempFile);
            $attributes['image_file_id'] = $thumbnailTemp->entityId();
            // Delete temp file after done
            upload()->rollUp($attributes['image_file_id']);
        }

        $attributes = array_merge($attributes, [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
        ]);

        /** @var Model $model */
        $model = $this->getModel()->newModelInstance();
        $model->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $model->setPrivacyListAttribute($attributes['list']);
        }

        $model->save();

        $this->attachmentRepository()->updateItemId(Arr::get($attributes, 'attachments', []), $model);

        $model->refresh();

        return $model;
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->withUserMorphTypeActiveScope()->getModel()->newQuery()
            ->where('is_featured', HasFeature::IS_FEATURED)
            ->where('is_approved', '=', 1)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->withUserMorphTypeActiveScope()->getModel()->newQuery()
            ->where('is_sponsor', \MetaFox\Platform\Contracts\HasSponsor::IS_SPONSOR)
            ->where('is_approved', '=', 1)
            ->simplePaginate($limit);
    }

    public function updatePlaylist(ContractUser $context, int $id, array $attributes): Model
    {
        $removeThumbnail = Arr::get($attributes, 'remove_thumbnail', 0);

        $thumbTempFile = Arr::get($attributes, 'thumb_temp_file', 0);

        $playlist = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(PlaylistPolicy::class, 'update', $context, $playlist);

        $attributes['name'] = $this->cleanTitle($attributes['name']);

        if (null === Arr::get($attributes, 'description')) {
            Arr::set($attributes, 'description', MetaFoxConstant::EMPTY_STRING);
        }

        if ($removeThumbnail) {
            $oldFile = $playlist->image_file_id;
            app('storage')->deleteFile($oldFile, null);
            $attributes['image_file_id'] = null;
        }

        if ($thumbTempFile > 0) {
            $tempFile                    = upload()->getFile($thumbTempFile);
            $attributes['image_file_id'] = $tempFile->entityId();
            // Delete temp file after done
            upload()->rollUp($thumbTempFile);
        }

        $playlist->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $playlist->setPrivacyListAttribute($attributes['list']);
        }

        $playlist->save();

        $this->attachmentRepository()->updateItemId(Arr::get($attributes, 'attachments', []), $playlist);

        $playlist->refresh();

        return $playlist;
    }

    public function viewPlaylist(ContractUser $context, int $id): Model
    {
        $playlist = $this
            ->withUserMorphTypeActiveScope()
            ->with(['user', 'userEntity', 'attachments'])
            ->find($id);

        policy_authorize(PlaylistPolicy::class, 'view', $context, $playlist);

        $playlist->incrementTotalView();

        return $playlist->refresh();
    }

    public function deletePlaylist(ContractUser $context, int $id): bool
    {
        $playlist = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(PlaylistPolicy::class, 'delete', $context, $playlist);

        if (!$playlist->delete()) {
            return false;
        }

        return true;
    }

    public function viewPlaylistItems(ContractUser $context, int $id, array $attributes = []): Paginator
    {
        $playlist = $this->withUserMorphTypeActiveScope()->find($id);

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        if (null === $playlist->owner) {
            return new \Illuminate\Pagination\Paginator([], $limit);
        }

        /**
         * @var Builder $query
         */
        $query = resolve(SongRepositoryInterface::class)->getModel()->newQuery();

        $query->join('music_playlist_data', function (JoinClause $joinClause) use ($id) {
            $joinClause->on('music_playlist_data.item_id', '=', 'music_songs.id')
                ->where('music_playlist_data.playlist_id', '=', $id);
        });

        /**
         * @var PrivacyScope $privacyScope
         */
        $privacyScope = resolve(PrivacyScope::class)
            ->setUserId($context->entityId())
            ->setModerationPermissionName('music_song.moderate');

        return $query->addScope($privacyScope)
            ->simplePaginate($limit, ['music_songs.*']);
    }

    public function addSong(ContractUser $context, int $playlistId, int $songId): PlaylistData
    {
        policy_authorize(PlaylistPolicy::class, 'create', $context, $context);

        $playlistData = new PlaylistData([
            'playlist_id' => $playlistId,
            'item_id'     => $songId,
            'ordering'    => $this->getNextOrdering($playlistId),
        ]);

        $playlistData->save();

        return $playlistData;
    }

    private function getNextOrdering(int $playlistId): int
    {
        $currentOrdering = PlaylistData::query()
            ->where('playlist_id', '=', $playlistId)
            ->orderByDesc('ordering')
            ->first();

        if (null === $currentOrdering) {
            return 1;
        }

        return (int) $currentOrdering->ordering + 1;
    }

    public function getPlaylistOptions(ContractUser $context): array
    {
        $playlists = $this->getModel()->newQuery()
            ->where('user_id', '=', $context->entityId())
            ->orderBy('name')
            ->get();

        return $playlists->map(function (Playlist $item) {
            return ['label' => ban_word()->clean($item->name), 'value' => $item->id];
        })->toArray();
    }

    public function getPlaylistByItemId(ContractUser $context, int $itemId): Collection
    {
        return $this->getModel()->newQuery()
            ->where('user_id', '=', $context->entityId())
            ->get();
    }
}
