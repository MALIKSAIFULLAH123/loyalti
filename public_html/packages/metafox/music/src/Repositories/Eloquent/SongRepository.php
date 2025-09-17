<?php

namespace MetaFox\Music\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song as Model;
use MetaFox\Music\Policies\GenrePolicy;
use MetaFox\Music\Policies\SongPolicy;
use MetaFox\Music\Repositories\GenreDataRepositoryInterface;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Music\Support\Browse\Scopes\Genre\GenreScope;
use MetaFox\Music\Support\Browse\Scopes\Song\MusicAlbumScope;
use MetaFox\Music\Support\Browse\Scopes\Song\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Song\ViewScope;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacyMember;
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
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;
use Owenoj\LaravelGetId3\GetId3;

/**
 * Class SongRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class SongRepository extends AbstractRepository implements SongRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use HasSponsorInFeed;
    use UserMorphTrait;
    use CollectTotalItemStatTrait;

    public function model()
    {
        return Model::class;
    }

    private function genreRepository(): GenreRepositoryInterface
    {
        return resolve(GenreRepositoryInterface::class);
    }

    private function attachmentRepository(): AttachmentRepositoryInterface
    {
        return resolve(AttachmentRepositoryInterface::class);
    }

    public function viewSongs(ContractUser $context, ContractUser $owner, array $attributes): Paginator
    {
        $limit = $attributes['limit'];

        $view = $attributes['view'];

        $this->withUserMorphTypeActiveScope();

        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature($limit);
        }

        if (Browse::VIEW_PENDING == $view) {
            if (Arr::get($attributes, 'user_id') == 0 || Arr::get($attributes, 'user_id') != $context->entityId()) {
                if ($context->isGuest() || !$context->hasPermissionTo('music_song.approve')) {
                    throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
                }
            }
        }

        $query = $this->buildQueryViewSongs($context, $owner, $attributes);

        return $query
            ->with(['album', 'isFavorite', 'genres'])
            ->simplePaginate($limit, ['music_songs.*']);
    }

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @throws AuthorizationException
     */
    private function buildQueryViewSongs(ContractUser $context, ContractUser $owner, array $attributes): Builder
    {
        $sort       = Arr::get($attributes, 'sort');
        $sortType   = Arr::get($attributes, 'sort_type');
        $when       = Arr::get($attributes, 'when');
        $view       = Arr::get($attributes, 'view');
        $search     = Arr::get($attributes, 'q');
        $profileId  = Arr::get($attributes, 'user_id');
        $genreId    = Arr::get($attributes, 'genre_id');
        $isFeatured = Arr::get($attributes, 'is_featured');

        if ($context->entityId() && $profileId == $context->entityId() && $view != Browse::VIEW_PENDING) {
            $view = Browse::VIEW_MY;
        }

        if ($genreId > 0) {
            $category = $this->genreRepository()->find($genreId);

            policy_authorize(GenrePolicy::class, 'viewActive', $context, $category);
        }
        /**
         * @var PrivacyScope $privacyScope
         */
        $privacyScope = resolve(PrivacyScope::class)
            ->setUserId($context->entityId())
            ->setModerationPermissionName('music_song.moderate')
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
            ->setView($view)
            ->setProfileId($profileId);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query->addScope(resolve(SearchScope::class, ['query' => $search, 'fields' => ['name']]));
        }

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());
            $viewScope->setIsViewOwner(true);
            $query->where('music_songs.is_approved', '=', 1);
        }

        if ($genreId) {
            $query->addScope(resolve(GenreScope::class, [
                'itemType' => Model::ENTITY_TYPE,
                'genreId'  => $genreId,
            ]));
        }

        $musicAlbumScope = new MusicAlbumScope();
        $musicAlbumScope->setUserContext($context);
        $query->addScope($musicAlbumScope);

        $query->addScope(new FeaturedScope($isFeatured));

        $query = $this->applyDisplaySongSetting($query, $owner, $view);

        if (!$isFeatured) {
            $query->addScope($privacyScope);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }

    /**
     * @param  Builder                 $query
     * @param  ContractUser            $owner
     * @param  string                  $view
     * @return Builder
     * @throws AuthenticationException
     */
    private function applyDisplaySongSetting(Builder $query, ContractUser $owner, string $view): Builder
    {
        if ($view == Browse::VIEW_MY) {
            return $query;
        }

        /*
         * Does not support view pending items from Group in My Pending Photos
         */
        if (!$owner instanceof HasPrivacyMember) {
            $query->where('music_songs.owner_type', '=', $owner->entityType());
        }

        return $query;
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

    /**
     * @param  ContractUser           $context
     * @param  ContractUser           $owner
     * @param  array                  $attributes
     * @return Model
     * @throws AuthorizationException
     */
    public function createSong(ContractUser $context, ContractUser $owner, array $attributes): Model
    {
        policy_authorize(SongPolicy::class, 'create', $context, $owner);

        app('flood')->checkFloodControlWhenCreateItem($context, Model::ENTITY_TYPE);

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

            $attributes = array_merge($attributes, [
                'description' => $description,
            ]);
        }

        $tempFile = Arr::get($attributes, 'temp_file', 0);

        if ($tempFile > 0) {
            $musicTempFile              = upload()->getFile($tempFile);
            $attributes['song_file_id'] = $musicTempFile->entityId();

            $attributes['duration'] = $this->getSongDuration($musicTempFile->entityId());
            // Delete temp file after done
            upload()->rollUp($tempFile);
        }

        $thumbTempFile = Arr::get($attributes, 'thumb_temp_file', 0);

        if ($thumbTempFile > 0) {
            $thumbnailTemp               = upload()->getFile($thumbTempFile);
            $attributes['image_file_id'] = $thumbnailTemp->entityId();

            // Delete temp file after done
            upload()->rollUp($attributes['image_file_id']);
        }

        $attributes['name'] = $this->cleanTitle($attributes['name']);

        /*
         * TODO: improve this for next version because we does not have song_set to group songs into a feed for approval in Group
         */
        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'is_approved' => (int) policy_check(SongPolicy::class, 'autoApprove', $context, $owner),
        ]);

        if ($owner->hasPendingMode()) {
            $attributes['is_approved'] = 1;
        }

        /** @var Model $model */
        $model = $this->getModel()->newModelInstance();

        $model->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $model->setPrivacyListAttribute($attributes['list']);
        }

        $model->save();

        resolve(GenreDataRepositoryInterface::class)->updateData($model, $attributes['genres']);

        if (!empty($attributes['attachments'])) {
            $this->attachmentRepository()->updateItemId($attributes['attachments'], $model);
        }

        $model->refresh();

        return $model;
    }

    public function getSongDuration(int $songFileId): int
    {
        $songFile = app('storage')->getAs($songFileId);

        // Using a third party library to parse song data.
        // @TODO: Remove this library after moving FFMPEG into a core package.
        $track    = new GetId3($songFile);
        $duration = $track->getPlaytimeSeconds();

        return round($duration);
    }

    public function updateSong(ContractUser $context, int $id, array $attributes): Model
    {
        $removeThumbnail = Arr::get($attributes, 'remove_thumbnail', 0);

        $thumbTempFile = Arr::get($attributes, 'thumb_temp_file', 0);

        $song = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(SongPolicy::class, 'update', $context, $song);

        if (Arr::has($attributes, 'privacy') && !$context->can('updatePrivacy', [$song, $attributes['privacy']])) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
        }

        if (isset($attributes['name'])) {
            $attributes['name'] = $this->cleanTitle($attributes['name']);
        }

        if (Arr::has($attributes, 'description')) {
            $description = Arr::get($attributes, 'description');

            if (null === $description) {
                $description = MetaFoxConstant::EMPTY_STRING;
            }

            $attributes = array_merge($attributes, [
                'description' => $description,
            ]);
        }

        $tempFile = Arr::get($attributes, 'temp_file', 0);

        if ($tempFile > 0) {
            $musicTempFile              = upload()->getFile($tempFile);
            $attributes['song_file_id'] = $musicTempFile->entityId();

            $attributes['duration'] = $this->getSongDuration($musicTempFile->entityId());

            // Delete temp file after done
            upload()->rollUp($attributes['song_file_id']);

            app('storage')->deleteFile($song->song_file_id, null);
        }

        if ($removeThumbnail) {
            $oldFile = $song->image_file_id;
            app('storage')->deleteFile($oldFile, null);
            $attributes['image_file_id'] = null;
        }

        if ($thumbTempFile > 0) {
            $tempFile = upload()->getFile($thumbTempFile);

            $attributes['image_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($thumbTempFile);
        }

        $song->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $song->setPrivacyListAttribute($attributes['list']);
        }

        $song->save();

        resolve(GenreDataRepositoryInterface::class)->updateData($song, $attributes['genres']);

        if (!empty($attributes['attachments'])) {
            $this->attachmentRepository()->updateItemId($attributes['attachments'], $song);
        }

        $song->refresh();

        $this->updateFeedStatus($song);

        return $song;
    }

    protected function updateFeedStatus(Model $song): void
    {
        app('events')->dispatch('activity.feed.mark_as_pending', [$song]);
    }

    public function viewSong(ContractUser $context, int $id): Model
    {
        $query = $this->getModel()
            ->newQuery()
            ->with(['user', 'userEntity', 'genres', 'attachments']);

        $musicAlbumScope = new MusicAlbumScope();
        $musicAlbumScope->setUserContext($context);
        $query->addScope($musicAlbumScope);

        $song = $query->findOrFail($id);

        policy_authorize(SongPolicy::class, 'view', $context, $song);

        $song->incrementTotalView();

        return $song->refresh();
    }

    public function deleteSong(ContractUser $context, int $id): bool
    {
        $song = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(SongPolicy::class, 'delete', $context, $song);

        if (!$song->delete()) {
            return false;
        }

        return true;
    }

    public function downloadSong(ContractUser $context, int $id): Model
    {
        $song = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(SongPolicy::class, 'download', $context, $song);

        $song->incrementAmount('total_download');

        return $song;
    }

    public function updateTotalPlay(Model $song): bool
    {
        $song->incrementAmount('total_play');

        return true;
    }

    public function removeFromPlaylist(Model $song, Playlist $playlist): bool
    {
        $song->playlists()->detach([$playlist->entityId()]);

        return true;
    }
}
