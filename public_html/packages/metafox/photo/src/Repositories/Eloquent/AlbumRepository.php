<?php

namespace MetaFox\Photo\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Photo\Jobs\DeleteAlbumJob;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Policies\AlbumPolicy;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Browse\Scopes\Album\PrivacyScope as AlbumPrivacyScope;
use MetaFox\Photo\Support\Browse\Scopes\Album\SortScope;
use MetaFox\Photo\Support\Browse\Scopes\Album\ViewScope;
use MetaFox\Photo\Support\Facades\Album as Facade;
use MetaFox\Photo\Support\Traits\FloodControlPhotoTrait;
use MetaFox\Platform\Contracts\HasAvatar;
use MetaFox\Platform\Contracts\HasCover;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BrowseSearchScope;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Traits\UserMorphTrait;
use Throwable;

/**
 * Class AlbumRepository.
 *
 * @property Album $model
 * @method   Album getModel()
 * @method   Album find($id, $columns = ['*'])
 * @mixin UserMorphTrait;
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AlbumRepository extends AbstractRepository implements AlbumRepositoryInterface
{
    use HasFeatured;
    use HasSponsor;
    use HasApprove;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;
    use HasSponsorInFeed;
    use FloodControlPhotoTrait;

    public function model(): string
    {
        return Album::class;
    }

    protected function groupRepository(): PhotoGroupRepositoryInterface
    {
        return resolve(PhotoGroupRepositoryInterface::class);
    }

    protected function photoRepository(): PhotoRepositoryInterface
    {
        return resolve(PhotoRepositoryInterface::class);
    }

    public function viewAlbums(User $context, User $owner, array $attributes = []): Paginator
    {
        $view = $attributes['view'] ?? Browse::VIEW_ALL;

        $limit = !empty($attributes['limit']) ? $attributes['limit'] : Pagination::DEFAULT_ITEM_PER_PAGE;

        $this->withUserMorphTypeActiveScope();
        switch ($view) {
            case 'feature':
                return $this->findFeature($limit);
        }

        $query = $this->buildQueryViewAlbums($context, $owner, $attributes);

        return $query
            ->with(['albumText', 'coverPhoto', 'userEntity', 'ownerEntity'])
            ->simplePaginate($limit, ['photo_albums.*']);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildQueryViewAlbums(User $context, User $owner, array $attributes): Builder
    {
        $sort       = $attributes['sort'] ?? Browse::SORT_RECENT;
        $sortType   = $attributes['sort_type'] ?? Browse::SORT_TYPE_DESC;
        $when       = $attributes['when'] ?? Browse::WHEN_ALL;
        $view       = $attributes['view'] ?? Browse::VIEW_ALL;
        $search     = $attributes['q'] ?? null;
        $profileId  = $attributes['user_id'] ?? 0;
        $isFeatured = Arr::get($attributes, 'is_featured');

        if (!$context->isGuest()) {
            if ($profileId == $context->entityId()) {
                $view = Browse::VIEW_MY;
            }
        }

        // Scopes.
        $privacyScope = new PrivacyScope();
        $privacyScope->setUserId($context->entityId())
            ->setModerationPermissionName('photo_album.moderate');
        $privacyScope->setHasUserBlock(true);

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view);

        $query = $this->getModel()->newQuery();

        $this->applyDisplayPhotoSetting($query, $context, $owner, $attributes);

        if ($owner instanceof HasPrivacyMember) {
            if (!$owner->isMember($context) && !$context->hasSuperAdminRole()) {
                $query->where('photo_albums.total_item', '>', 0);
            }
        }

        $ownerId = null;

        if ($owner->entityId() != $context->entityId()) {
            $ownerId = $owner->entityId();
        }

        if ($profileId > 0 && $profileId != $context->entityId()) {
            $ownerId = $profileId;
        }

        if (null !== $ownerId) {
            $privacyScope->setOwnerId($ownerId);

            $viewScope->setIsViewOwner(true);
        }

        if ($search != null) {
            $query->where('photo_albums.album_type', Album::NORMAL_ALBUM);

            $query->leftJoin('photo_album_text', function (JoinClause $clause) {
                $clause->on('photo_album_text.id', '=', 'photo_albums.id');
            });

            $searchScope = resolve(BrowseSearchScope::class, [
                'text'            => $search,
                'plainTextFields' => [
                    'photo_albums.name' => [
                        'required' => true,
                    ],
                ],
                'htmlFields' => [
                    'photo_album_text.text_parsed' => [
                        'required' => false,
                    ],
                ]
            ]);

            $query->addScope($searchScope);
        }

        $query->addScope(new FeaturedScope($isFeatured));

        if (!$isFeatured) {
            $query->addScope($privacyScope);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }

    public function viewAlbum(User $context, int $id): Album
    {
        $this->withUserMorphTypeActiveScope();
        $album = $this->withUserMorphTypeActiveScope()->with(['albumText', 'coverPhoto', 'userEntity', 'ownerEntity'])->find($id);

        policy_authorize(AlbumPolicy::class, 'view', $context, $album);

        $album->incrementAmount('total_view');

        return $album;
    }

    /**
     * @throws AuthorizationException
     * @throws PermissionDeniedException
     */
    public function createAlbum(User $context, User $owner, array $attributes): Album
    {
        policy_authorize(AlbumPolicy::class, 'create', $context, $owner);

        app('events')->dispatch('photo.album.pre_photo_album_create', [$context, $attributes], true);

        // Quota check for album
        $quotaCheckData = [
            'message' => __p('photo::phrase.album_quota_limit_reached'),
            'where'   => [
                'album_type' => 0,
            ],
        ];
        app('quota')->checkQuotaControlWhenCreateItem(
            $context,
            Album::ENTITY_TYPE,
            1,
            $quotaCheckData
        );

        // flood check for album
        app('flood')->checkFloodControlWhenCreateItem($context, Album::ENTITY_TYPE);
        $this->checkFloodControlWhenCreatePhoto($context, $attributes, 'items.' . MetaFoxConstant::FILE_NEW_STATUS);

        $newItems       = Arr::get($attributes, 'items.' . MetaFoxConstant::FILE_NEW_STATUS, []);
        $updateItems    = Arr::get($attributes, 'items.' . MetaFoxConstant::FILE_UPDATE_STATUS, []);
        $newCount       = is_array($newItems) ? count($newItems) : 0;
        $updateCount    = is_array($updateItems) ? count($updateItems) : 0;
        $createNewGroup = $newCount + $updateCount > 0;

        unset($attributes['items']);

        // Check photo quota per items create + remove
        $this->checkPhotoQuota($context, $newItems);

        $attributes = array_merge($attributes, [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
            'module_id'  => Photo::ENTITY_TYPE,
            'album_type' => Arr::get($attributes, 'album_type', Album::NORMAL_ALBUM),
        ]);

        $album = $this->getModel()->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $album->setPrivacyListAttribute($attributes['list']);
        }
        $album->save();

        $groupParams = [
            'user_id'     => $album->userId(),
            'user_type'   => $album->userType(),
            'owner_id'    => $album->ownerId(),
            'owner_type'  => $album->ownerType(),
            'album_id'    => $album->entityId(),
            'privacy'     => $album->privacy,
            'list'        => $album->getPrivacyListAttribute(),
            'is_approved' => 0,
            'content'     => null,
        ];

        $group = $createNewGroup ? $this->newPhotoGroup($groupParams) : null;

        //Handle add new album items
        if (!empty($newItems)) {
            $this->uploadAlbumItems($context, $album, $group, $newItems, $attributes);
        }

        if (!empty($updateItems)) {
            $this->syncItemsWithAlbum($context, $album, $group, $updateItems);
        }

        $this->integratePhotoGroup($group);
        $album->refresh();

        return $album;
    }

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Album
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function updateAlbum(User $context, int $id, array $attributes): Album
    {
        $album = $this->withUserMorphTypeActiveScope()->with(['items'])->find($id);

        policy_authorize(AlbumPolicy::class, 'update', $context, $album);

        if (Arr::has($attributes, 'privacy') && !$context->can('updatePrivacy', [$album, $attributes['privacy']])) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
        }

        app('events')->dispatch('photo.album.pre_photo_album_update', [$context, $attributes], true);
        $this->checkFloodControlWhenCreatePhoto($context, $attributes, 'items.' . MetaFoxConstant::FILE_NEW_STATUS);

        $fileItems   = collect(Arr::get($attributes, 'items', []));
        $newItems    = $fileItems->get(MetaFoxConstant::FILE_NEW_STATUS, []);
        $updateItems = $fileItems->get(MetaFoxConstant::FILE_UPDATE_STATUS, []);
        $removeItems = $fileItems->get(MetaFoxConstant::FILE_REMOVE_STATUS, []);

        Arr::forget($attributes, 'items');

        // Check photo quota
        $this->checkPhotoQuota($context, $newItems, $removeItems);

        $album->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $album->setPrivacyListAttribute($attributes['list']);
        }

        $album->save();

        $groupParams = [
            'user_id'     => $album->userId(),
            'user_type'   => $album->userType(),
            'owner_id'    => $album->ownerId(),
            'owner_type'  => $album->ownerType(),
            'album_id'    => $album->entityId(),
            'privacy'     => $album->privacy,
            'list'        => $album->getPrivacyListAttribute(),
            'is_approved' => 0,
            'content'     => null,
        ];

        $photoGroup = $this->shouldCreateNewPhotoGroup($album, $newItems, $updateItems)
            ? $this->newPhotoGroup($groupParams)
            : null;

        $owner = Arr::get($attributes, 'owner');
        if ($owner instanceof User) {
            $attributes['owner_id']   = $owner->entityId();
            $attributes['owner_type'] = $owner->entityType();
        }

        $attributes = array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'module_id' => Photo::ENTITY_TYPE,
        ]);

        //Handle add new album items
        if (!empty($newItems)) {
            $this->uploadAlbumItems($context, $album, $photoGroup, $newItems, $attributes);
        }

        // Add below items as album
        if (!empty($updateItems)) {
            $this->syncItemsWithAlbum($context, $album, $photoGroup, $updateItems);
        }

        //Handle remove old album items
        if (!empty($removeItems)) {
            $this->removeAlbumItems($context, $removeItems);
        }

        $this->integratePhotoGroup($photoGroup);
        $album->refresh();

        return $album;
    }

    private function shouldCreateNewPhotoGroup(Album $album, array $newItems, array $updateItems): bool
    {
        if (count($newItems) > 0) {
            return true;
        }

        /**
         * When a photo is added to an album by selecting it from the 'Select from my photos' option,
         * the photo's status within the 'items' object is marked as 'update' rather than 'create'.
         * So we need to check any photo exist in $updateItems have been upload via "Select from my photos" ?
         */
        $albumItemIds  = $album->items()->pluck('item_id')->toArray();
        $updateItemIds = Arr::pluck($updateItems, 'id');

        $selectFromMyPhotoIds = array_diff($updateItemIds, $albumItemIds);

        return count($selectFromMyPhotoIds);
    }

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return array
     * @throws AuthorizationException
     */
    public function uploadMedias(User $context, int $id, array $attributes): array
    {
        $album = $this->withUserMorphTypeActiveScope()->find($id);
        if ($album->privacy == MetaFoxPrivacy::CUSTOM) {
            $album->loadPrivacyListAttribute();
        }

        policy_authorize(AlbumPolicy::class, 'uploadMedias', $context, $album);

        app('events')->dispatch('photo.album.pre_photo_album_upload_media', [$context, $attributes], true);
        $this->checkFloodControlWhenCreatePhoto($context, $attributes, 'items.' . MetaFoxConstant::FILE_NEW_STATUS);

        $fileItems      = collect(Arr::get($attributes, 'items', []));
        $newItems       = $fileItems->get(MetaFoxConstant::FILE_NEW_STATUS, []);
        $updateItems    = $fileItems->get(MetaFoxConstant::FILE_UPDATE_STATUS, []);
        $removeItems    = $fileItems->get(MetaFoxConstant::FILE_REMOVE_STATUS, []);
        $newCount       = is_array($newItems) ? count($newItems) : 0;
        $updateCount    = is_array($updateItems) ? count($updateItems) : 0;
        $removeCount    = is_array($removeItems) ? count($removeItems) : 0;
        $createNewGroup = $newCount + $updateCount - $removeCount > 0;

        Arr::forget($attributes, 'items');

        // Check photo quota
        $this->checkPhotoQuota($context, $newItems, $removeItems);

        $owner = Arr::get($attributes, 'owner', null);

        $attributes = array_merge($attributes, [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $owner instanceof User ? $owner->entityId() : 0,
            'owner_type' => $owner instanceof User ? $owner->entityType() : 'user',
            'module_id'  => Photo::ENTITY_TYPE,
        ]);

        $photoGroup = $createNewGroup ? $this->newPhotoGroup([
            'user_id'     => $album->userId(),
            'user_type'   => $album->userType(),
            'owner_id'    => $album->ownerId(),
            'owner_type'  => $album->ownerType(),
            'content'     => null,
            'album_id'    => $album->entityId(),
            'privacy'     => $album->privacy,
            'list'        => $album->getPrivacyListAttribute(),
            'is_approved' => 0,
        ]) : null;

        $newItemStatistic = [];
        $updatedPhoto     = 0;
        $updatedVideo     = 0;

        //Handle add new album items
        if (!empty($newItems)) {
            $newItemStatistic = $this->uploadAlbumItems($context, $album, $photoGroup, $newItems, $attributes);
        }

        // Add below items as album
        if (!empty($updateItems)) {
            [$updatedPhoto, $updatedVideo] = $this->syncItemsWithAlbum($context, $album, $photoGroup, $updateItems);
        }

        //Handle remove old album items
        if (!empty($removeItems)) {
            $this->removeAlbumItems($context, $removeItems);
        }

        $this->integratePhotoGroup($photoGroup);
        $album->refresh();

        return [
            'album'          => $album,
            'updated_photo'  => $updatedPhoto,
            'updated_video'  => $updatedVideo,
            'pending_photo'  => Arr::get($newItemStatistic, 'pending.photo', 0),
            'pending_video'  => Arr::get($newItemStatistic, 'pending.video', 0),
            'uploaded_photo' => Arr::get($newItemStatistic, 'uploaded.photo', 0),
            'uploaded_video' => Arr::get($newItemStatistic, 'uploaded.video', 0),
        ];
    }

    public function deleteAlbum(User $context, int $id): bool
    {
        $album = $this->withUserMorphTypeActiveScope()->find($id);

        DeleteAlbumJob::dispatch($context, $album);

        return true;
    }

    public function deleteAlbumAndPhotos(User $context, Album $album): bool
    {
        if (!$album->forceDelete()) {
            return false;
        }

        Photo::query()
            ->where('album_id', '=', $album->entityId())
            ->lazy()
            ->each(function (mixed $photo) {
                if (!$photo instanceof Photo) {
                    return true;
                }

                $photo->delete();
            });

        return true;
    }

    /**
     * @param int $limit
     *
     * @return Paginator
     * @todo implement cache
     */
    public function findFeature(int $limit = 4): Paginator
    {
        //Todo: check privacy ?
        return $this->getModel()->newModelInstance()
            ->with('albumText')
            ->where('total_item', '>', 0)
            ->where('is_featured', '=', Album::IS_FEATURED)
            ->where('is_approved', '=', Album::IS_APPROVED)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    /**
     * @param int $limit
     *
     * @return Paginator
     * @todo implement cache
     */
    public function findSponsor(int $limit = 4): Paginator
    {
        //Todo: check privacy ?
        return $this->getModel()->newModelInstance()
            ->with('albumText')
            ->where('total_item', '>', 0)
            ->where('is_sponsor', '=', Album::IS_SPONSOR)
            ->where('is_approved', '=', Album::IS_APPROVED)
            ->simplePaginate($limit);
    }

    public function updateAlbumCover(Album $album, int $photoId = 0): void
    {
        $photo = $photoId > 0
            ? $this->photoRepository()->getModel()->where('id', $photoId)->first()
            : $album->approvedItems()->where('item_type', Photo::ENTITY_TYPE)->latest()->first()?->detail;

        if (!$photo instanceof Photo) {
            return;
        }

        if (!$photo->isApproved()) {
            return;
        }

        /*
         * Not need to trigger updated observer
         */
        $album->updateQuietly(['cover_photo_id' => $photo->entityId()]);
    }

    /**
     * @param Builder $query
     * @param User    $owner
     * @param array   $attributes
     *
     * @return void
     */
    private function applyDisplayPhotoSetting(Builder $query, User $context, User $owner, array $attributes): void
    {
        $hasPrivacyMember = $owner instanceof HasPrivacyMember;

        match ($hasPrivacyMember) {
            true  => $this->queryAlbumForPrivacyMember($query, $owner),
            false => $this->queryAlbumForUserProfile(
                $query,
                $context,
                Arr::get($attributes, 'view', Browse::VIEW_ALL),
                Arr::get($attributes, 'user_id', 0)
            ),
        };
    }

    protected function queryAlbumForPrivacyMember(Builder $query, ?User $owner = null): void
    {
        $specialTypes = [];

        $entityType = $owner instanceof User ? $owner->entityType() : 'photo';

        if (!Settings::get("{$entityType}.display_profile_photo_within_gallery", false)) {
            $specialTypes[] = Album::PROFILE_ALBUM;
        }

        if (!Settings::get("{$entityType}.display_cover_photo_within_gallery", false)) {
            $specialTypes[] = Album::COVER_ALBUM;
        }

        // Some apps do not have this setting
        if (!Settings::get("{$entityType}.display_timeline_photo_within_gallery", true)) {
            $specialTypes[] = Album::TIMELINE_ALBUM;
        }

        if (count($specialTypes)) {
            $query->whereNotIn('photo_albums.album_type', $specialTypes);
        }
    }

    protected function queryAlbumForUserProfile(Builder $query, User $context, string $view, int $profileId): void
    {
        if ($profileId) {
            return;
        }

        if ($view == Browse::VIEW_MY) {
            return;
        }

        $this->queryAlbumForPrivacyMember($query);

        $ownerTypes = [];

        if (!Settings::get('photo.display_photo_album_created_in_page', false)) {
            $ownerTypes[] = 'page';
        }

        if (!Settings::get('photo.display_photo_album_created_in_group', false)) {
            $ownerTypes[] = 'group';
        }

        if (count($ownerTypes)) {
            $query->whereNotIn('photo_albums.owner_type', $ownerTypes);
        }
    }

    /**
     * @param User $context
     * @param User $owner
     *
     * @return array<int,             mixed>
     */
    public function getAlbumsForForm(User $context, User $owner): array
    {
        $albums = $this->getModel()->newModelInstance()
            ->where('album_type', Album::NORMAL_ALBUM)
            ->where('owner_id', $owner->entityId())
            ->where('owner_type', $owner->entityType())
            ->where('is_approved', '=', 1)
            ->get()
            ->collect();

        $albumData = [];

        foreach ($albums as $album) {
            /* @var Album $album */
            $albumData[] = [
                'label' => ban_word()->clean(__p($album->name)),
                'value' => $album->entityId(),
            ];
        }

        return $albumData;
    }

    public function viewAlbumItems(User $context, int $id, array $attributes = []): Paginator
    {
        /**
         * @var Album $album
         */
        $album = $this->getModel()->query()->findOrFail($id);

        policy_authorize(AlbumPolicy::class, 'view', $context, $album);

        $limit = !empty($attributes['limit']) ? $attributes['limit'] : Pagination::DEFAULT_ITEM_PER_PAGE;

        $page = $this->resolvePageForItemDetail($context, $album, $attributes);

        $query = $this->buildQueryAlbumItems($context, $album, $attributes);

        return $query->paginate($limit, ['photo_album_item.*'], 'page', $page);
    }

    protected function resolvePageForItemDetail(User $context, Album $album, array $attributes = []): int
    {
        $mediaId = Arr::get($attributes, 'media_id');

        $sort = Arr::get($attributes, 'sort', Browse::SORT_RECENT);

        $sortType = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_DESC);

        if (!is_numeric($mediaId) || $sort != Browse::SORT_RECENT) {
            return (int) Arr::get($attributes, 'page', 1);
        }

        /**
         * @var AlbumItem $item
         */
        $item = AlbumItem::query()
            ->where([
                'item_id'  => $mediaId,
                'album_id' => $album->entityId(),
            ])
            ->firstOrFail();

        $builder = $this->buildQueryAlbumItems($context, $album, $attributes);

        match ($sortType) {
            Browse::SORT_TYPE_ASC => $builder->where('photo_album_item.id', '<=', $item->entityId()),
            default               => $builder->where('photo_album_item.id', '>=', $item->entityId()),
        };

        $total = $builder->count();

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        if (!$total || $total <= $limit) {
            return 1;
        }

        $page = $total / $limit;

        $surplus = $total % $limit;

        if (0 === $surplus) {
            return $page;
        }

        return $page + 1;
    }

    /**
     * @param User                 $context
     * @param Album                $album
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function buildQueryAlbumItems(User $context, Album $album, array $attributes): Builder
    {
        $sort = $attributes['sort'] ?? Browse::SORT_RECENT;

        $sortType = $attributes['sort_type'] ?? Browse::SORT_TYPE_DESC;

        $query = AlbumItem::query();

        if (!$context->hasPermissionTo('photo_album.moderate')) {
            // Scopes.
            $privacyScope = new AlbumPrivacyScope();

            $privacyScope->setUserId($context->entityId());

            $query->addScope($privacyScope);
        }

        $sortScope = new SortScope();

        $sortScope->setSort($sort)->setSortType($sortType);

        $query->where('photo_album_item.album_id', '=', $album->entityId());

        $query->where(function (Builder $builder) use ($context, $album) {
            app('events')->dispatch('photo.query_album_items', [$context, $album, $builder], true);
        });

        return $query
            ->whereHas('detail')
            ->addScope($sortScope);
    }

    /**
     * @param User                             $context
     * @param Album                            $album
     * @param PhotoGroup|null                  $group
     * @param array<int, array<string, mixed>> $newItems
     * @param array<string, mixed>             $params
     *
     * @return array
     */
    protected function uploadAlbumItems(User $context, Album $album, ?PhotoGroup $group, array $newItems, array $params = []): array
    {
        $owner = $album->owner;
        if (null === $owner || null == $group) {
            return [0, 0];
        }

        $params = array_merge($params, [
            'is_approved' => 0,
            'content'     => null,
            'text'        => null,
            'group_id'    => $group->entityId(),
            'album_id'    => $album->entityId(),
            'album_type'  => $album->album_type,
            'files'       => $newItems,
            'module_id'   => 'photo',
        ]);

        $params = $this->photoRepository()->getPrivacyFromAlbum($album->entityId(), $params);

        /*
         * In case owner has pending mode, is_approved is depend on pending mode status
         */
        if ($owner->hasPendingMode()) {
            $params['is_approved'] = (int) $group->isApproved();
        }

        $statistic = [];
        foreach ($newItems as $item) {
            $tempFile = upload()->getFile($item['id']);

            $newParams = array_merge($params, Arr::get($item, 'extra_info', []));

            $itemResult = app('events')->dispatch('photo.media_upload', [
                $context,
                $album->owner,
                $tempFile->item_type,
                $tempFile,
                $newParams,
            ], true);

            $this->handleUploadStatistic($item, $itemResult, $statistic);
        }

        return $statistic;
    }

    /**
     * @param User              $context
     * @param Album             $album
     * @param PhotoGroup|null   $group
     * @param array<int, mixed> $updateItems
     *
     * @return array
     */
    protected function syncItemsWithAlbum(User $context, Album $album, ?PhotoGroup $group, array $updateItems): array
    {
        $updatedStatistic = ['photo' => 0, 'video' => 0];
        $albumItemIds     = $album->items->pluck('item_id');

        foreach ($updateItems as $item) {
            if ($this->shouldSkipAlbumItem($albumItemIds, $item)) {
                continue;
            }

            $data = $this->prepareItemDataForUpdate($album, $item, $group);

            app('events')->dispatch('photo.media_add_to_album', [$context, $data], true);

            $updatedStatistic[$item['type']]++;
        }

        return [$updatedStatistic['photo'], $updatedStatistic['video']];
    }

    private function shouldSkipAlbumItem(Collection $albumItemIds, array $item): bool
    {
        if (!$albumItemIds->contains($item['id'] ?? 0)) {
            return false;
        }

        if (Arr::has($item, 'extra_info')) {
            return false;
        }

        return true;
    }

    private function prepareItemDataForUpdate(Album $album, array $item, ?PhotoGroup $group = null): array
    {
        $data = array_merge(
            [
                'id'         => Arr::get($item, 'id', 0),
                'type'       => Arr::get($item, 'type'),
                'album_id'   => Arr::get($item, 'album_id', $album->entityId()),
                'album_type' => Arr::get($item, 'album_type', $album->album_type),
                'privacy'    => $album->privacy,
            ],
            Arr::get($item, 'extra_info', [])
        );

        if ($group instanceof PhotoGroup) {
            $data['group_id'] = $group->entityId();
        }

        $data = $this->removeMatureAttributeIfNeeded($album, $data);

        if (MetaFoxPrivacy::CUSTOM == $data['privacy']) {
            Arr::set($data, 'list', $album->getPrivacyListAttribute());
        }

        return $data;
    }

    private function removeMatureAttributeIfNeeded(Album $album, array $data): array
    {
        if (!in_array($album->album_type, [Album::COVER_ALBUM, Album::PROFILE_ALBUM])) {
            return $data;
        }

        if (!Arr::has($data, 'mature')) {
            return $data;
        }

        Arr::forget($data, 'mature');

        return $data;
    }

    /**
     * @param User              $context
     * @param array<int, mixed> $removeItems
     *
     * @return void
     */
    protected function removeAlbumItems(User $context, array $removeItems): void
    {
        foreach ($removeItems as $item) {
            $modelClass = Relation::getMorphedModel($item['type']);

            if (!$modelClass || !class_exists($modelClass)) {
                continue;
            }

            /** @var Model $modelInstance */
            $modelInstance = resolve($modelClass);

            if (!$modelInstance instanceof Model) {
                continue;
            }

            $itemModel = $modelInstance
                ->newQuery()
                ->where($modelInstance->getKeyName(), $item['id'])->first();

            if (!$itemModel instanceof Model) {
                continue;
            }

            if (!$context->can('delete', [$itemModel, $itemModel])) {
                continue;
            }

            app('events')->dispatch('photo.media_remove', [$item['id'], $item['type']], true);
        }
    }

    public function getDefaultUserAlbums(int $ownerId, array $types = []): Collection
    {
        if (!count($types)) {
            $types = Facade::getDefaultTypes();
        }

        return $this->getModel()->newQuery()
            ->whereIn('album_type', $types)
            ->where('owner_id', $ownerId)
            ->get();
    }

    public function isDefaultUserAlbum(int $id, int $ownerId = 0): bool
    {
        $where = [
            'id' => $id,
        ];

        if ($ownerId) {
            Arr::set($where, 'owner_id', $ownerId);
        }

        $exists = $this->getModel()->newQuery()
            ->whereIn('album_type', Facade::getDefaultTypes())
            ->where($where)
            ->count(['id']);

        return $exists == 1;
    }

    public function getAlbumById(int $id): ?Album
    {
        return $this->getModel()->newModelQuery()
            ->where('id', '=', $id)
            ->first();
    }

    /**
     * @param User              $context
     * @param array<int, mixed> $newItems
     * @param array<int, mixed> $removedItems
     *
     * @return void
     */
    private function checkPhotoQuota(User $context, array $newItems = [], array $removedItems = []): void
    {
        if (empty($newItems) && empty($removedItems)) {
            return;
        }

        $totalNew = collect($newItems)->groupBy('type')->map(function ($item) {
            return count($item);
        })->get('photo', 0);

        $totalRemove = collect($removedItems)->groupBy('type')->map(function ($item) {
            return count($item);
        })->get('photo', 0);

        app('quota')->checkQuotaControlWhenCreateItem($context, Photo::ENTITY_TYPE, $totalNew - $totalRemove);
    }

    public function feature(User $context, int $id, int $feature): bool
    {
        $model = $this->withUserMorphTypeActiveScope()->find($id);

        if ($model instanceof HasPrivacy && $model->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        if (!$model->items()->count() && !$model->is_featured) {
            abort(401, __p('photo::phrase.cannot_feature_empty_album'));
        }

        gate_authorize($context, 'feature', $model, $model, $feature);

        return $model->update(['is_featured' => $feature]);
    }

    /**
     * @param Album $album
     * @param Photo $photo
     *
     * @return void
     */
    public function removeAvatarFromAlbum(Album $album, Photo $photo): void
    {
        if (Album::PROFILE_ALBUM != $album->album_type) {
            return;
        }

        $owner = $photo->owner;
        if ($owner instanceof HasUserProfile) {
            $owner = $owner->profile;
        }

        if (!$owner instanceof HasAvatar) {
            return;
        }

        if ($owner->getAvatarId() != $photo->entityId()) {
            return;
        }

        if ($owner instanceof UserProfile) {
            $photo->owner->update([
                'updated_at' => Carbon::now(), //to update user => trigger toUserResource
                'profile'    => $owner->getAvatarDataEmpty(),
            ]);
        }

        if (!$owner instanceof UserProfile) {
            $owner->update($owner->getAvatarDataEmpty());
        }
    }

    /**
     * @param Album $album
     * @param Photo $photo
     *
     * @return void
     */
    public function removeCoverFromAlbum(Album $album, Photo $photo): void
    {
        if (Album::COVER_ALBUM != $album->album_type) {
            return;
        }

        $owner = $photo->owner;
        if ($owner instanceof HasUserProfile) {
            $owner = $owner->profile;
        }

        if (!$owner instanceof HasCover) {
            return;
        }

        if ($owner->getCoverId() == $photo->entityId()) {
            $owner->update($owner->getCoverDataEmpty());
        }
    }

    private function handleUploadStatistic(array $item, mixed $itemResult, array &$statistic): void
    {
        if (empty($statistic)) {
            $statistic = [
                'uploaded' => [
                    'photo' => 0,
                    'video' => 0,
                ],
                'pending'  => [
                    'photo' => 0,
                    'video' => 0,
                ],
            ];
        }

        $allowTypes = ['photo', 'video'];
        $type       = Arr::get($item, 'type');
        if (!$type || !in_array($type, $allowTypes)) {
            return;
        }

        $statistic['uploaded'][$type]++;
        if ($itemResult && !$itemResult?->isApproved()) {
            $statistic['pending'][$type]++;
        }
    }

    /**
     * @param array<string, mixed> $groupParams
     *
     * @return PhotoGroup
     */
    protected function newPhotoGroup(array $groupParams): PhotoGroup
    {
        $photoGroup = $this->groupRepository()->getModel();
        $photoGroup->fill($groupParams);
        $list = Arr::get($groupParams, 'list');
        if ($list == MetaFoxPrivacy::CUSTOM) {
            $photoGroup->setPrivacyListAttribute($list);
        }

        $photoGroup->save();
        $photoGroup->refresh();

        return $photoGroup;
    }

    /**
     * @param PhotoGroup|null $group
     *
     * @return void
     */
    protected function integratePhotoGroup(?PhotoGroup $group): void
    {
        if (!$group instanceof PhotoGroup) {
            return;
        }

        // Update Photo Set approve status
        $this->groupRepository()->updateApprovedStatus($group);

        // Create Photo Group Feed
        app('events')->dispatch('activity.feed.create_from_resource', [$group], true);

        $group->refresh();
    }
}
