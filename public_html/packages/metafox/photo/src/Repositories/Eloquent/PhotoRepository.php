<?php

namespace MetaFox\Photo\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator as Paginate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use MetaFox\Core\Support\FileSystem\Image\Plugins\CopyImage;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Photo\Jobs\EmptyTrashJob;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Policies\AlbumPolicy;
use MetaFox\Photo\Policies\CategoryPolicy;
use MetaFox\Photo\Policies\PhotoGroupPolicy;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Photo\Policies\PhotoTagFriendPolicy;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Browse\Scopes\Photo\PhotoAlbumScope;
use MetaFox\Photo\Support\Browse\Scopes\Photo\PrivacyScope;
use MetaFox\Photo\Support\Browse\Scopes\Photo\SortScope;
use MetaFox\Photo\Support\Browse\Scopes\Photo\ViewScope;
use MetaFox\Photo\Support\Facades\Album as AlbumFacade;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasAvatarMorph;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasItemMorph;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\TagFriendModel;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BrowseSearchScope;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class PhotoRepository.
 * @method   Photo getModel()
 * @method   Photo find($id, $columns = ['*'])
 * @method   Photo newModelInstance()
 * @property Photo $model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PhotoRepository extends AbstractRepository implements PhotoRepositoryInterface
{
    use HasFeatured;
    use HasSponsor;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use HasFilterTagUserTrait;

    public function model(): string
    {
        return Photo::class;
    }

    /**
     * @return AlbumRepositoryInterface
     */
    private function getAlbumRepository(): AlbumRepositoryInterface
    {
        return resolve(AlbumRepositoryInterface::class);
    }

    /**
     * @return PhotoGroupRepositoryInterface
     */
    private function photoGroupRepository(): PhotoGroupRepositoryInterface
    {
        return resolve(PhotoGroupRepositoryInterface::class);
    }

    /**
     * @return UserRepositoryInterface
     */
    private function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    /**
     * @return CategoryRepositoryInterface
     */
    private function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    public function getAlbum(User $user, User $owner, int $albumType): Album
    {
        $data = [
            'owner_id'   => $owner->entityId(),
            'album_type' => $albumType,
        ];

        $value = [
            'user_id'     => $user->entityId(),
            'user_type'   => $user->entityType(),
            'owner_type'  => $owner->entityType(),
            'name'        => Album::ALBUM_NAME[$albumType],
            'description' => '',
        ];

        if ($albumType == Album::TIMELINE_ALBUM) {
            $value['user_id']   = $owner->entityId();
            $value['user_type'] = $owner->entityType();
        }

        /** @var Album $album */
        $album = $this->getAlbumRepository()->getModel()->newQuery()->firstOrCreate($data, $value);

        return $album->refresh();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createPhoto(
        User    $context,
        User    $owner,
        array   $attributes,
        int     $albumType = Album::TIMELINE_ALBUM,
        ?string $typeId = null
    ): array
    {
        //Don't check policy authorization here, check policy authorization at the beginning

        if (!Settings::get('photo.allow_photo_category_selection', true)) {
            unset($attributes['categories']);
        }

        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'module_id'   => Photo::ENTITY_TYPE,
            'is_approved' => (int) policy_check(PhotoPolicy::class, 'autoApprove', $context, $owner),
            'mature'      => Photo::MATURE_CONTENT_NO,
        ]);

        if ($this->checkUserUploadAvatarOrCover($typeId)) {
            $attributes['is_approved'] = 1;
        }

        $files = $attributes['files'];
        unset($attributes['files']);

        $photoIds = [];

        if (empty($attributes['album_id']) && $albumType != Album::NORMAL_ALBUM) {
            $album                  = $this->getAlbum($context, $owner, $albumType);
            $attributes['album_id'] = $album->entityId();
        }

        if (!empty($attributes['album_id'])) {
            $attributes = $this->getPrivacyFromAlbum($attributes['album_id'], $attributes);
        }

        foreach ($files as $key => $file) {
            $rawFile  = $file['file'] ?? null;
            $tempFile = $file['temp_file'] ?? 0;

            $imageFile = $tempFile ? upload()->getFile($tempFile) : $this->uploadRawFile($context, $rawFile, $file);

            if (!$imageFile instanceof StorageFile) {
                continue;
            }

            $data = [
                'allow_rate'    => (empty($attributes['album_id']) ? 1 : 0),
                'content'       => $file['description'] ?? '',
                'ordering'      => $key,
                'image_file_id' => $imageFile->entityId(),
                'title'         => $imageFile->original_name,
            ];

            $newAttributes = array_merge($attributes, $data);

            /** @var Photo $photo */
            $photo = $this->getModel()->newModelInstance();
            $photo->fill($newAttributes);

            if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
                $photo->setPrivacyListAttribute($attributes['list']);
            }

            if ($typeId !== null) {
                $photo->setActivityTypeIdAttribute($typeId);
            }

            $photo->save();

            $photo->refresh();

            $photoIds[] = $photo->entityId();
        }

        return $photoIds;
    }

    private function checkUserUploadAvatarOrCover(?string $type): bool
    {
        $photoProfileList = [
            UserModel::USER_UPDATE_COVER_ENTITY_TYPE,
            UserModel::USER_UPDATE_AVATAR_ENTITY_TYPE,
            UserModel::USER_AVATAR_SIGN_UP,
        ];

        return in_array($type, $photoProfileList);
    }

    public function updatePhoto(User $context, int $id, array $attributes): Photo
    {
        $photo = $this
            ->withUserMorphTypeActiveScope()
            ->with(['group', 'album'])
            ->find($id);

        policy_authorize(PhotoPolicy::class, 'update', $context, $photo);

        if (!policy_check(PhotoPolicy::class, 'updateAlbum', $context, $photo)) {
            unset($attributes['album_id']);
        }

        $photoGroupAttributes = null;

        $hasGroup = null !== $photo->group;

        if ($hasGroup) {
            $photoGroupAttributes = $attributes;
        }

        match ($hasGroup) {
            true  => $this->prepareDataForGroupUpdate($photo->group, $photo, $attributes, $photoGroupAttributes),
            false => $this->setContent($attributes)
        };

        $attributes = $this->photoGroupRepository()->handleContent($attributes, 'text');

        if (Arr::has($attributes, 'album')) {
            $attributes = $this->getPrivacyFromAlbum($attributes['album'], $attributes, $photo);

            //If edit album of photo, we will prioritize album's privacy.
            //Thus, reset photo group privacy back to old privacy
            if ($this->shouldResetPhotoGroupPrivacy($attributes, $photoGroupAttributes)) {
                $photoGroupAttributes['privacy'] = $photo->privacy;
            }
        }

        $photo->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $photo->setPrivacyListAttribute($attributes['list']);
        }

        $photo->save();
        $photo->refresh();

        //Update photo group
        if (is_array($photoGroupAttributes)) {
            app('events')->dispatch('photo.update_photo_group', [$context, $photo->group, $photoGroupAttributes], true);
        }

        // In case must not update content/text of photo but need to searching this photo like feed
        if (Arr::has($attributes, 'searchable_text')) {
            if (null === $photo->getFeedContent()) {
                $this->updateGlobalSearch($photo, Arr::get($attributes, 'searchable_text'));
            }
        }

        if (Arr::has($attributes, 'base64')) {
            $this->handleBase64($photo, Arr::get($attributes, 'base64'));
        }

        if (Arr::has($attributes, 'tagged_friends')) {
            $this->handleMultipleTagFriends($photo, Arr::get($attributes, 'tagged_friends'));
        }

        return $photo;
    }

    private function shouldResetPhotoGroupPrivacy(array $attributes, array|null $photoGroupAttributes): bool
    {
        if (!is_array($photoGroupAttributes)) {
            return false;
        }

        if (null === Arr::get($attributes, 'privacy')) {
            return false;
        }

        if (null === Arr::get($photoGroupAttributes, 'privacy')) {
            return false;
        }

        return Arr::get($attributes, 'privacy') != Arr::get($photoGroupAttributes, 'privacy');
    }

    protected function handleBase64(Photo $photo, string $base64): void
    {
        $uploadFile = upload()->convertBase64ToUploadedFile($base64);

        if (!$uploadFile instanceof UploadedFile) {
            return;
        }

        $file = upload()
            ->setStorage('photo')
            ->setPath('photo')
            ->setThumbSizes(ResizeImage::SIZE)
            ->setItemType('photo')
            ->setUser($photo->user)
            ->storeFile($uploadFile);

        if (null === $file) {
            return;
        }

        app('storage')->rolLDown($photo->image_file_id);

        $photo->fill([
            'image_file_id' => $file->entityId(),
        ]);

        $photo->updateQuietly();
    }

    protected function handleMultipleTagFriends(Photo $photo, array $tags): void
    {
        $oldTaggedFriends = app('events')->dispatch('friend.get_owner_tag_friends', [$photo], true);

        // In case Friend app is not active
        if (null === $oldTaggedFriends) {
            return;
        }

        //If no containing any friends, delete all tagged friends from photo
        if (!count($tags)) {
            app('events')->dispatch('friend.delete_item_tag_friend', [$photo], true);

            return;
        }

        // Old friend ids that existed in database
        $oldFriendIds = $oldTaggedFriends
            ->pluck('owner_id')
            ->toArray();

        // current friend ids that waiting for processing
        $currentFriendIds = Arr::pluck($tags, 'user_id');

        // Filter with new tagged friends
        $keptFriendIds = array_diff($currentFriendIds, $oldFriendIds);

        // Filter existed tagged friends and merged with new tagged friends
        $keptFriendIds = array_merge($keptFriendIds, array_intersect($oldFriendIds, $currentFriendIds));

        // Filter deleted tagged friends
        $deletedFriendIds = array_diff($oldFriendIds, $currentFriendIds);

        $keptTaggedFriends = [];

        foreach ($tags as $tag) {
            if (in_array($tag['user_id'], $keptFriendIds)) {
                $keptTaggedFriends[] = [
                    'friend_id' => $tag['user_id'],
                    'px'        => Arr::get($tag, 'px', 0),
                    'py'        => Arr::get($tag, 'py', 0),
                ];
            }
        }

        if (count($deletedFriendIds)) {
            app('events')->dispatch('friend.delete_item_tag_friend', [$photo, $deletedFriendIds]);
        }

        $this->handleTaggedFriends($photo->user, $photo, $keptTaggedFriends);
    }

    protected function handleTaggedFriends(User $user, Photo $photo, array $keptTaggedFriends): bool
    {
        if (!count($keptTaggedFriends)) {
            return false;
        }

        $tagThemselves = array_filter($keptTaggedFriends, function ($keptTagged) use ($user) {
            return $user->entityId() === Arr::get($keptTagged, 'friend_id');
        });

        $extra = $this->transformTaggedFriends($user, $user, $user, $keptTaggedFriends);

        $keptTaggedFriends = Arr::get($extra, 'tagged_friends');

        if (!empty($tagThemselves)) {
            $keptTaggedFriends = array_merge($keptTaggedFriends, $tagThemselves);
        }

        if (!is_array($keptTaggedFriends) || !count($keptTaggedFriends)) {
            return false;
        }

        app('events')->dispatch('friend.create_tag_friends', [$user, $photo, $keptTaggedFriends], true);

        return true;
    }

    protected function unsetContent(array &$attributes)
    {
        if (!Arr::has($attributes, 'content')) {
            return;
        }

        unset($attributes['content']);
    }

    protected function setContent(array &$attributes): void
    {
        if (Arr::has($attributes, 'content')) {
            return;
        }

        if (Arr::has($attributes, 'text')) {
            Arr::set($attributes, 'content', Arr::get($attributes, 'text'));
        }
    }

    protected function prepareDataForGroupUpdate(
        ?PhotoGroup $photoGroup,
        Photo       $photo,
        array       &$attributes,
        ?array      &$groupAttributes
    ): void
    {
        if ($photoGroup->total_item != 1) {
            $this->setContent($attributes);
            $this->unsetContent($groupAttributes);

            return;
        }

        if (null !== $photo->content) {
            $this->setContent($attributes);
            $this->unsetContent($groupAttributes);

            return;
        }

        $text = Arr::get($attributes, 'text');

        unset($attributes['text']);

        if (Arr::has($attributes, 'searchable_text')) {
            return;
        }

        Arr::set($groupAttributes, 'content', $text);

        Arr::set($attributes, 'searchable_text', $text);
    }

    public function viewPhoto(User $context, int $id): Photo
    {
        $this->withUserMorphTypeActiveScope();
        $query = $this->getModel()->newQuery();
        $photo = $query->findOrFail($id);

        policy_authorize(PhotoPolicy::class, 'view', $context, $photo);

        if ($photo->album instanceof Album) {
            policy_authorize(AlbumPolicy::class, 'view', $context, $photo->album);
        }

        $photo->incrementTotalView();
        $photo->with(['photoInfo', 'user', 'userEntity', 'ownerEntity', 'categories']);

        return $photo->refresh();
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return array<string, mixed>
     */
    public function deletePhoto(User $context, int $id): array
    {
        $photo = $this->withUserMorphTypeActiveScope()->with(['group'])->find($id);

        $feed = $photo->activity_feed;

        $group = $photo->group;

        $hasGroup = $group instanceof Content;

        if ($hasGroup) {
            $feed = $group->activity_feed;
        }

        $album = $photo->album;
        $photo->delete();

        if ($hasGroup) {
            $this->photoGroupRepository()->updateGlobalSearchForSingleMedia(
                $group,
                $group->content,
                $group->total_item > 1 ? $group->total_item - 1 : 0
            );
        }

        EmptyTrashJob::dispatch($context->entityType(), $context->entityId(), [$photo->entityId()]);

        if ($album instanceof Album) {
            $album->refresh();
        }

        return [
            'id'      => $photo->entityId(),
            'album'   => ResourceGate::asItem($album, false),
            'feed_id' => $feed?->entityId(),
        ];
    }

    public function findFeature(int $limit = 4): Paginator
    {
        //todo check setting: display_cover_photo_within_gallery, display_profile_photo_within_gallery, display_timeline_photo_within_gallery, photo_mature_age_limit
        return $this->getModel()->newQuery()
            ->where('is_featured', Photo::IS_FEATURED)
            ->where('is_approved', Photo::IS_APPROVED)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        //todo check setting: display_cover_photo_within_gallery, display_profile_photo_within_gallery, display_timeline_photo_within_gallery, photo_mature_age_limit
        return $this->getModel()->newQuery()
            ->where('is_sponsor', Photo::IS_SPONSOR)
            ->where('is_approved', Photo::IS_APPROVED)
            ->simplePaginate($limit);
    }

    protected function getPrivacyForRemovingAlbum(?Photo $photo, array $attributes): array
    {
        if (null === $photo) {
            return $attributes;
        }

        // In case photo does not belong to any album before
        if ($photo->album_id == 0) {
            return $attributes;
        }

        // Does not allow to remove album from photo in case this album is default
        if (AlbumFacade::isDefaultAlbum($photo->album_type)) {
            unset($attributes['album']);

            return $attributes;
        }

        //In case remove album from photo which belongs to an album before, then this photo will be forced to belong to timeline album
        return array_merge($attributes, [
            'album_id'   => 0,
            'album_type' => Album::NORMAL_ALBUM,
            'privacy'    => $this->determinePrivacyForRemovingAlbum($photo, $attributes),
        ]);
    }

    private function determinePrivacyForRemovingAlbum(Photo $photo, array $attributes): int
    {
        $privacy = Arr::get($attributes, 'privacy');

        if (null !== $privacy) {
            return $privacy;
        }

        if ($photo->owner instanceof PostBy) {
            return $photo->owner->getPrivacyPostBy();
        }

        // Reset to everyone privacy in case owner does not has own privacy
        return MetaFoxPrivacy::EVERYONE;
    }

    protected function getPrivacyForDefaultAlbum(Album $album, ?Photo $photo, array $attributes): array
    {
        if (!$photo instanceof Photo) {
            return $this->getPrivacyForNormalAlbum($album, $attributes);
        }

        // In case update to another album but this album is default, we will not allow to update
        if ($photo->album_id != $album->entityId()) {
            unset($attributes['album']);

            // In case current album of photo is normal album, we will not allow to update privacy
            if (!AlbumFacade::isDefaultAlbum($photo->album_type)) {
                unset($attributes['privacy']);
            }
        }

        return $attributes;
    }

    protected function getPrivacyForNormalAlbum(Album $album, array $attributes): array
    {
        $attributes['privacy'] = $album->privacy;

        if ($album->privacy == MetaFoxPrivacy::CUSTOM) {
            $attributes['privacy'] = $album->privacy;

            $lists = PrivacyPolicy::getPrivacyItem($album);

            $listIds = [];

            if (is_array($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $attributes['list'] = $listIds;
        }

        return $attributes;
    }

    public function getPrivacyFromAlbum(?int $albumId, array $attributes, ?Photo $photo = null): array
    {
        if (null === $albumId) {
            return $this->getPrivacyForRemovingAlbum($photo, $attributes);
        }

        if ($albumId == 0) {
            return $this->getPrivacyForRemovingAlbum($photo, $attributes);
        }

        /** @var Album $album */
        $album = $this->getAlbumRepository()->find($albumId);

        if ($album->is_default) {
            return $this->getPrivacyForDefaultAlbum($album, $photo, $attributes);
        }

        return $this->getPrivacyForNormalAlbum($album, $attributes);
    }

    public function viewPhotos(User $context, User $owner, array $attributes = []): Paginator
    {
        $limit = !empty($attributes['limit']) ? $attributes['limit'] : Pagination::DEFAULT_ITEM_PER_PAGE;

        if (isset($attributes['feed_id'])) {
            return $this->getPhotoByFeedId($context, $attributes['feed_id'], $attributes);
        }

        policy_authorize(PhotoPolicy::class, 'viewAny', $context, $owner);

        $attributes['view'] = $attributes['view'] ?? Browse::VIEW_ALL;

        if ($attributes['view'] == Browse::VIEW_PENDING) {
            policy_authorize(PhotoPolicy::class, 'viewApproveListing', $context, $owner);
        }

        $this->withUserMorphTypeActiveScope();

        switch ($attributes['view']) {
            case 'feature':
                return $this->findFeature($limit);
        }

        $categoryId = Arr::get($attributes, 'category_id', 0);

        if ($categoryId > 0) {
            $category = $this->categoryRepository()->find($categoryId);

            policy_authorize(CategoryPolicy::class, 'viewActive', $context, $category);
        }

        $query     = $this->buildQueryPhotos($context, $owner, $attributes);
        $relations = ['photoInfo', 'group', 'album'];

        return $query
            ->with($relations)
            ->simplePaginate($limit, ['photos.*']);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     *
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function buildQueryPhotos(User $context, User $owner, array $attributes): Builder
    {
        $view       = $attributes['view'] ?? Browse::VIEW_ALL;
        $sort       = $attributes['sort'] ?? Browse::SORT_RECENT;
        $sortType   = $attributes['sort_type'] ?? Browse::SORT_TYPE_DESC;
        $when       = $attributes['when'] ?? Browse::WHEN_ALL;
        $categoryId = $attributes['category_id'] ?? null;
        $search     = $attributes['q'] ?? null;
        $profileId  = $attributes['user_id'] ?? 0;
        $isFeatured = Arr::get($attributes, 'is_featured');

        if (!$context->isGuest()) {
            if ($profileId == $context->entityId() && !in_array($view, [Browse::VIEW_PENDING, ViewScope::VIEW_TAG_WALL])) {
                $view = Browse::VIEW_MY;
            }
        }

        $album = null;
        if (!empty($attributes['album_id'])) {
            $album = $this->getAlbumRepository()->find($attributes['album_id']);
            policy_authorize(AlbumPolicy::class, 'view', $context, $album);
        }

        if (!empty($attributes['group_id'])) {
            $photoGroup = PhotoGroup::query()->findOrFail($attributes['group_id']);
            policy_authorize(PhotoGroupPolicy::class, 'view', $context, $photoGroup);
        }

        // Scopes.
        $privacyScope = new PrivacyScope();
        $privacyScope->setUserId($context->entityId())
            ->setModerationPermissionName('photo.moderate');
        $privacyScope->setHasUserBlock(true);

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view)->setProfileId($profileId);

        $query = $this->getModel()->newQuery();

        if ($album == null && empty($attributes['group_id'])) {
            $query = $this->applyDisplayPhotoSetting($query, $owner, $view);
        }

        if ($album != null) {
            $query->where('photos.album_id', '=', $album->entityId());
        }

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());
            $viewScope->setIsViewOwner(true);

            if ($view === ViewScope::VIEW_TAG_WALL) {
                $privacyScope->setSkipQueryOwner(true);
            }

            if ($owner instanceof UserModel && $view !== ViewScope::VIEW_TAG_WALL) {
                $query->where('photos.user_id', $owner->entityId());
            }

            if (!$context->can('approve', [Photo::class, resolve(Photo::class)])) {
                $query->where('photos.is_approved', '=', Photo::IS_APPROVED);
            }
        }

        if ($categoryId != null) {
            $categoryScope = new CategoryScope();

            if (!is_array($categoryId)) {
                $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
            }

            $categoryScope->setCategories($categoryId);
            $query = $query->addScope($categoryScope);
        }

        if (null != $search) {
            $query->leftJoin('photo_info', function (JoinClause $clause) {
                $clause->on('photo_info.id', '=', 'photos.id');
            });

            $searchScope = resolve(BrowseSearchScope::class, [
                'text'            => $search,
                'plainTextFields' => [
                    'photos.title' => [
                        'required' => true,
                    ],
                    'photo_info.text_parsed' => [
                        'required' => false,
                    ],
                ],
            ]);

            $query->addScope($searchScope);
        }

        if (isset($attributes['group_id'])) {
            $query = $query->where('group_id', $attributes['group_id']);
        }

        $query->addScope(new FeaturedScope($isFeatured));

        $photoAlbumScope = new PhotoAlbumScope();
        $photoAlbumScope->setUserContext($context);
        $query->addScope($photoAlbumScope);

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
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function applyDisplayPhotoSetting(Builder $query, User $owner, string $view): Builder
    {
        $context             = user();
        $condition           = [];
        $albumProfileCond    = ['photos.album_type', '<>', Album::PROFILE_ALBUM];
        $albumCoverCondition = ['photos.album_type', '<>', Album::COVER_ALBUM];
        $allowViews          = [Browse::VIEW_MY, Browse::VIEW_PENDING, Browse::VIEW_MY_PENDING];

        if ($owner instanceof HasPrivacyMember) {
            if (!Settings::get("{$owner->entityType()}.display_profile_photo_within_gallery", false)) {
                $condition[] = $albumProfileCond;
            }

            if (!Settings::get("{$owner->entityType()}.display_cover_photo_within_gallery", false)) {
                $condition[] = $albumCoverCondition;
            }

            if (!empty($condition)) {
                $query->where($condition);
            }
        }

        if (!$owner instanceof HasPrivacyMember && !in_array($view, $allowViews)) {
            if (!Settings::get('photo.display_profile_photo_within_gallery', false)) {
                $condition[] = $albumProfileCond;
            }

            if (!Settings::get('photo.display_cover_photo_within_gallery', false)) {
                $condition[] = $albumCoverCondition;
            }

            if (!Settings::get('photo.display_timeline_photo_within_gallery', false)) {
                $condition[] = ['photos.album_type', '<>', Album::TIMELINE_ALBUM];
            }

            $condition[] = ['photos.owner_type', $context->entityType()];

            if (!empty($condition)) {
                $query->where($condition);
            }
        }

        return $query;
    }

    public function tempFileToPhoto(User $user, User $owner, TempFileModel $tempFile, array $params = []): Photo
    {
        policy_authorize(PhotoPolicy::class, 'create', $user, $owner);

        /**
         * MUST NOT ASSIGN AN EMPTY STRING TO $TEXT WHEN $TEXT IS NULL.
         * Rule: If a photo group contains only one item and the content of that photo is not NULL (including an empty string),
         * we will use the content of that photo. Otherwise, we will use the content of the entire photo group.
         * @see \MetaFox\Photo\Http\Resources\v1\Photo\EditPhotoForm::getDescription()
         */
        $text = Arr::get($params, 'text');

        $params = array_merge($params, [
            'item_type'     => $tempFile->item_type,
            'image_file_id' => $tempFile->id,
            'dir_name'      => $tempFile->dir_name,
            'user_id'       => $user->entityId(),
            'user_type'     => $user->entityType(),
            'owner_id'      => $owner->entityId(),
            'owner_type'    => $owner->entityType(),
            'title'         => Arr::get($params, 'title', $tempFile->original_name),
            'text'          => $text,
            'content'       => $text,
            'original_name' => $tempFile->original_name,
            'file_name'     => $tempFile->file_name,
            'file_size'     => $tempFile->file_size,
            'mime_type'     => $tempFile->mime_type,
            'extension'     => $tempFile->extension,
            'width'         => $tempFile->width,
            'height'        => $tempFile->height,
            'is_approved'   => policy_check(PhotoPolicy::class, 'autoApprove', $user, $owner),
        ]);

        /*
         * You must push all tagged friends when add/delete more items
         * In case you push an empty array, then all tagged friends will be removed
         */
        if (Arr::has($params, 'tagged_friends')) {
            if (!is_array($params['tagged_friends'])) {
                Arr::set($params, 'tagged_friends', []);
            }
        }

        $photo = new Photo();

        $photo->fill($params);

        if ($params['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $photo->setPrivacyListAttribute($params['list']);
        }

        $photo->save();

        $photo->refresh();

        if (Arr::has($params, 'searchable_text')) {
            $this->updateGlobalSearch($photo, Arr::get($params, 'searchable_text'));
        }

        //Override listener models.creating from Core
        $albumId = Arr::get($params, 'album_id', 0);
        if ($albumId > 0 && $user->userId() != $owner->userId()) {
            $album = $this->getAlbumRepository()->find($albumId);
            if (policy_check(AlbumPolicy::class, 'update', $user, $album)) {
                $photo->update(['privacy' => $album->privacy]);
            }
        }

        $tempFile->rollUp();

        if (Arr::has($params, 'tagged_friends')) {
            $this->handleMultipleTagFriends($photo, Arr::get($params, 'tagged_friends'));
        }

        return $photo;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function tempFileToExistPhoto(
        User          $user,
        User          $owner,
        Photo         $photo,
        TempFileModel $tempFile,
        array         $params = []
    ): Photo
    {
        policy_authorize(PhotoPolicy::class, 'update', $user, $photo);

        $params = array_merge($params, [
            'item_type'      => $tempFile->item_type,
            'image_file_id'  => $tempFile->id,
            'dir_name'       => $tempFile->dir_name,
            'title'          => $tempFile->original_name,
            'original_name'  => $tempFile->original_name,
            'file_name'      => $tempFile->file_name,
            'file_size'      => $tempFile->file_size,
            'mime_type'      => $tempFile->mime_type,
            'extension'      => $tempFile->extension,
            'width'          => $tempFile->width,
            'height'         => $tempFile->height,
            'tagged_friends' => !empty($params['tagged_friends']) ? $params['tagged_friends'] : null,
        ]);

        $photo->fill($params);

        if ($params['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $photo->setPrivacyListAttribute($params['list']);
        }

        $photo->save();

        $tempFile->rollUp();

        $photo->photoInfo?->refresh();
        $photo->refresh();

        return $photo;
    }

    public function getPhotoByFeedId(User $context, int $feedId, array $attributes = []): Paginator
    {
        $limit = !empty($attributes['limit']) ? $attributes['limit'] : Pagination::DEFAULT_ITEM_PER_PAGE;

        $defaultPaginator = new Paginate([], $limit);

        if (!app_active('metafox/activity')) {
            return $defaultPaginator;
        }

        /** @var mixed $feed */
        $feed = app('events')->dispatch('activity.get_feed_id', [$context, $feedId], true);

        //        try {

        //        } catch (AuthorizationException $authorizationException) {
        //            abort(403, __p('core::validation.unable_to_view_this_item_due_to_privacy'));
        //        }

        if (!$feed instanceof HasItemMorph) {
            return $defaultPaginator;
        }

        $model = $feed->item;

        if (!$model instanceof Content) {
            return $defaultPaginator;
        }

        if (!$model instanceof Photo && !$model instanceof PhotoGroup) {
            return $defaultPaginator;
        }

        /** @var User $owner */
        $owner = $model->owner;

        if ($model instanceof Photo) {
            return new Paginate([$model], $limit);
        }

        if ($model instanceof PhotoGroup) {
            $attributes['group_id'] = $model->entityId();
        }

        unset($attributes['feed_id']);

        return $this->viewPhotos($context, $owner, $attributes);
    }

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function makeProfileCover(User $context, int $id, array $attributes = []): bool
    {
        $photo        = $this->withUserMorphTypeActiveScope()->find($id);
        $ownerId      = $attributes['user_id'];
        $owner        = UserEntity::getById($ownerId)->detail;
        $policyMethod = match ($owner instanceof HasPrivacyMember) {
            true  => 'setParentCover',
            false => 'setProfileCover'
        };

        policy_authorize(PhotoPolicy::class, $policyMethod, $context, $photo);

        if (!$context instanceof HasUserProfile) {
            return false;
        }

        if ($photo->entityId() == $context->profile->getCoverId()) {
            $message = json_encode([
                'title'   => __p('core::phrase.oops'),
                'message' => __p(
                    'photo::phrase.the_photo_has_already_made_as_their_cover_picture',
                    ['their' => 'your']
                ),
            ]);
            abort(403, $message);
        }

        $params = [
            'cover_id'      => $photo['id'],
            'cover_type'    => 'photo',
            'cover_file_id' => $photo['image_file_id'],
            'position'      => $attributes['position'] ?? 0,
        ];

        $image = match ($photo->album_type) {
            Album::COVER_ALBUM => null,
            default            => $this->uploadProfileCover($photo)
        };

        if ($image instanceof UploadedFile) {
            $params['image'] = $image;
        }

        $type = $owner->entityType();

        app('events')->dispatch("$type.update_cover", [$context, $owner, $params]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function makeProfileAvatar(User $context, int $id, ?string $imageCrop = null): ?array
    {
        $photo = $this->withUserMorphTypeActiveScope()->with('photoInfo')->find($id);

        policy_authorize(PhotoPolicy::class, 'setProfileAvatar', $context, $photo);

        if (!$context instanceof HasUserProfile) {
            return null;
        }

        // the same file.
        if ($photo->entityId() == $context->profile->getAvatarId() && null === $imageCrop) {
            $message = json_encode([
                'title'   => __p('core::phrase.oops'),
                'message' => __p('photo::phrase.the_photo_has_already_made_as_your_profile_picture'),
            ]);
            abort(403, $message);
        }

        if (Album::PROFILE_ALBUM == $photo->album_type) {
            $uploadedCropFile = $imageCrop ? upload()->convertBase64ToUploadedFile($imageCrop) : null;

            $storageFile = $uploadedCropFile ? upload()
                ->setThumbSizes(['50x50', '120x120', '200x200'])
                ->setPath('page')
                ->storeFile($uploadedCropFile) : null;

            $avatarParams = [
                'avatar_id'      => $photo->entityId(),
                'avatar_type'    => 'photo',
                'avatar_file_id' => $storageFile instanceof StorageFile ? $storageFile->entityId() : $photo->image_file_id,
            ];

            return app('events')->dispatch('user.update_avatar', [$context, $context, $avatarParams], true);
        }

        // do not work with cloud disk because remote is not here
        $image = app('storage')->asUploadedFile($photo->image_file_id);

        if (false == $image) {
            return null;
        }

        $params = [
            'image'      => $image,
            'image_crop' => $imageCrop ?? upload()->convertImageToBase64($image->getRealPath()),
        ];

        return $this->userRepository()->uploadAvatar($context, $context, $params);
    }

    public function makeParentCover(User $context, int $id): bool
    {
        $photo = $this->withUserMorphTypeActiveScope()->with('owner')->find($id);

        policy_authorize(PhotoPolicy::class, 'setParentCover', $context, $photo);

        $owner = $photo->owner;

        if (!$owner instanceof HasCoverMorph) {
            return false;
        }

        if ($photo->entityId() == $owner->getCoverId()) {
            $message = json_encode([
                'title'   => __p('core::phrase.oops'),
                'message' => __p(
                    'photo::phrase.the_photo_has_already_made_as_their_cover_picture',
                    ['their' => $owner->entityType()]
                ),
            ]);
            abort(403, $message);
        }

        $params = [
            'cover_id'      => $photo['id'],
            'cover_type'    => 'photo',
            'cover_file_id' => $photo['image_file_id'],
            'position'      => $attributes['position'] ?? 0,
        ];

        $image = match ($photo->album_type) {
            Album::COVER_ALBUM => null,
            default            => $this->uploadProfileCover($photo)
        };

        if ($image instanceof UploadedFile) {
            $params['image'] = $image;
        }

        $result = app('events')->dispatch(
            "{$photo->ownerType()}.update_cover",
            [$context, $photo->owner, $params],
            true
        );

        if (empty($result)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function makeParentAvatar(User $context, int $id, ?User $owner = null, ?string $imageCrop = null): ?array
    {
        $photo = $this->withUserMorphTypeActiveScope()->with('owner')->find($id);
        $owner = $owner ?? $photo->owner;

        policy_authorize(PhotoPolicy::class, 'setParentAvatar', $context, $photo);

        if (!$owner instanceof HasAvatarMorph) {
            return null;
        }

        if ($photo->entityId() == $owner->getAvatarId() && null === $imageCrop) {
            $message = json_encode([
                'title'   => __p('core::phrase.oops'),
                'message' => __p(
                    'photo::phrase.the_photo_has_already_made_as_their_profile_picture',
                    ['their' => $owner->entityType()]
                ),
            ]);
            abort(403, $message);
        }

        if (Album::PROFILE_ALBUM == $photo->album_type) {
            $uploadedCropFile = $imageCrop ? upload()->convertBase64ToUploadedFile($imageCrop) : null;

            $storageFile = $uploadedCropFile ? upload()
                ->setThumbSizes(['50x50', '120x120', '200x200'])
                ->setPath('page')
                ->storeFile($uploadedCropFile) : null;

            $avatarParams = [
                'avatar_id'      => $photo->entityId(),
                'avatar_type'    => 'photo',
                'avatar_file_id' => $storageFile instanceof StorageFile ? $storageFile->entityId() : $photo->image_file_id,
            ];

            if ($owner instanceof HasAvatarMorph) {
                $owner->update($avatarParams);
            }

            $feed = null;

            try {
                /** @var Content $feed */
                $feed = app('events')->dispatch(
                    'activity.get_feed_by_item_id',
                    [$context, $owner->avatar_id, $owner->avatar_type, 'page_update_avatar'],
                    true
                );
                $feed->touch('created_at');

                app('events')->dispatch('activity.push_feed_on_top', [$feed->entityId()], true);
            } catch (Exception $e) {
                // Silent.
                Log::error($e->getMessage());
            }

            return [
                'user'    => $owner->refresh(),
                'feed_id' => $feed?->entityId() ?? 0,
            ];
        }

        $image = app('storage')->asUploadedFile($photo->image_file_id);
        if (!$image) {
            return null;
        }

        $imageCrop = $imageCrop ?? upload()->convertImageToBase64($image->getRealPath());
        $result    = app('events')->dispatch(
            "{$owner->entityType()}.update_avatar",
            [$context, $owner, $image, $imageCrop],
            true
        );

        if (!is_array($result)) {
            return [
                'user'    => $owner->refresh(),
                'feed_id' => 0,
            ];
        }

        return $result;
    }

    public function viewPhotoSet(User $user, int $id): PhotoGroup
    {
        /** @var PhotoGroup $photoSet */
        $photoSet = PhotoGroup::query()->findOrFail($id);

        policy_authorize(PhotoGroupPolicy::class, 'view', $user, $photoSet);

        $photoSet->with(['userEntity', 'ownerEntity']);

        return $photoSet;
    }

    /**
     * @param int $photoId
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function getTaggedFriends(User $context, int $photoId): Collection
    {
        $photo = $this->withUserMorphTypeActiveScope()->getModel()->newQuery()->find($photoId);
        if (null == $photo) {
            return new Collection([]);
        }

        policy_authorize(PhotoPolicy::class, 'view', $context, $photo);

        /** @var Collection $taggedFriends */
        $taggedFriends = app('events')->dispatch('friend.get_owner_tag_friends', [$photo], true);
        if (empty($taggedFriends)) {
            return new Collection([]);
        }

        return $taggedFriends;
    }

    public function tagFriend(User $user, User $friend, int $photoId, float $pxValue, float $pyValue): ?TagFriendModel
    {
        $photo = $this->withUserMorphTypeActiveScope()->find($photoId);

        policy_authorize(PhotoPolicy::class, 'tagFriend', $user, $friend, $photo);

        $params = [
            [
                'friend_id' => $friend->entityId(),
                'px'        => $pxValue,
                'py'        => $pyValue,
            ],
        ];

        $result = $this->handleTaggedFriends($user, $photo, $params);

        if (!$result) {
            return null;
        }

        /** @var TagFriendModel $data */
        $data = app('events')->dispatch('friend.get_tag_friend', [$photo, $friend], true);

        return $data;
    }

    /**
     * @param User $user
     * @param int  $tagId
     *
     * @return false|int
     * @throws AuthorizationException
     */
    public function deleteTaggedFriend(User $user, int $tagId)
    {
        /** @var TagFriendModel $taggedFriend */
        $taggedFriend = app('events')->dispatch('friend.get_tag_friend_by_id', [$tagId], true);
        if (empty($taggedFriend)) {
            return false;
        }

        $photo = $taggedFriend->item;
        if (!$photo instanceof Photo) {
            return false;
        }

        policy_authorize(PhotoTagFriendPolicy::class, 'removeTaggedFriend', $user, $taggedFriend);

        $result = app('events')->dispatch('friend.delete_tag_friend', [$tagId], true);
        if (empty($result)) {
            return false;
        }

        return $photo->entityId();
    }

    public function updateAvatarPath(int $photoId, string $path, array $sizes = [], array $squareSizes = []): string
    {
        $photo = $this->withUserMorphTypeActiveScope()->with(['photoInfo'])->find($photoId);

        $copyImage = new CopyImage(
            $photo->destination,
            $photo->server_id,
            $path,
            true,
            $sizes,
            $squareSizes
        );

        $newDestination = $copyImage->copy();

        $photo->update([
            'destination' => $newDestination,
            'file_name'   => mb_pathinfo($newDestination, PATHINFO_FILENAME),
            'dir_name'    => mb_pathinfo($newDestination, PATHINFO_DIRNAME),
        ]);

        return $newDestination;
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return PhotoGroup
     * @throws AuthorizationException
     */
    public function uploadMedias(User $context, User $owner, array $attributes): PhotoGroup
    {
        app('events')->dispatch('photo.pre_photo_upload_media', [$context, $attributes], true);

        $files = Arr::get($attributes, 'files', []);

        $this->checkPhotoQuota($context, $files);

        if (!Settings::get('photo.allow_photo_category_selection', true)) {
            unset($attributes['categories']);
        }

        if ($attributes['add_new_album'] == 1) {
            $album                  = $this->getAlbumRepository()->createAlbum($context, $owner, $attributes['new_album']);
            $attributes['album_id'] = $album->entityId();
        }

        if ($attributes['album_id'] > 0) {
            $attributes = $this->getPrivacyFromAlbum($attributes['album_id'], $attributes);
        }

        $groupParams = $this->preparePhotoGroupParamsForCreate($context, $owner, $attributes);

        $group = new PhotoGroup();
        $group->fill($groupParams);

        if ($group->privacy == MetaFoxPrivacy::CUSTOM) {
            $group->setPrivacyListAttribute($attributes['list']);
        }

        $group->save();
        $attributes['group_id'] = $group->entityId();

        foreach ($files as $file) {
            $tempFile   = upload()->getFile($file['id']);
            $attributes = array_merge($attributes, Arr::get($file, 'extra_info', []));
            $attributes = $this->preparePhotoParamsForCreate($attributes);

            app('events')->dispatch(
                'photo.media_upload',
                [$context, $owner, $tempFile->item_type, $tempFile, $attributes],
                true
            );
        }

        unset($attributes['files']);

        // Update photo group status after all of its items are
        $this->photoGroupRepository()->updateApprovedStatus($group);

        $group->refresh();

        return $group;
    }

    private function preparePhotoGroupParamsForCreate(User $context, User $owner, array $attributes): array
    {
        $groupParams = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'content'     => '',
            'is_approved' => 0,
        ]);

        $files = Arr::get($attributes, 'files', []);

        if (count($files) === 1) {
            $firstFile              = Arr::first($files);
            $groupParams['content'] = Arr::get($firstFile, 'extra_info.text', '');
        }

        return $groupParams;
    }

    private function preparePhotoParamsForCreate(array $attributes): array
    {
        $files = Arr::get($attributes, 'files', []);
        $text  = Arr::get($attributes, 'text');

        if (count($files) === 1) {
            unset($attributes['text']);

            Arr::set($attributes, 'searchable_text', $text);

            return $attributes;
        }

        Arr::set($attributes, 'content', $text);

        return $attributes;
    }

    public function getPhotosByGroupId(int $groupId): ?Collection
    {
        return $this->getModel()->newModelInstance()
            ->where([
                'group_id' => $groupId,
            ])
            ->get();
    }

    public function downloadPhoto(User $context, int $id): Photo
    {
        $photo = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(PhotoPolicy::class, 'download', $context, $photo);

        $photo->incrementAmount('total_download');

        return $photo;
    }

    /**
     * @param Photo $photo
     *
     * @return UploadedFile|null
     */
    protected function uploadProfileCover(Photo $photo): ?UploadedFile
    {
        // download image_file as uploaded file.
        $image = app('storage')->asUploadedFile($photo->image_file_id);

        if (!$image) {
            return null;
        }

        return $image;
    }

    protected function updateGlobalSearch(Photo $photo, ?string $text): ?bool
    {
        if (!$photo instanceof HasGlobalSearch) {
            return false;
        }

        $searchable = $photo->toSearchable();

        if (null === $searchable) {
            return false;
        }

        $searchable = array_merge($searchable, [
            'text' => $text ?? MetaFoxConstant::EMPTY_STRING,
        ]);

        return app('events')->dispatch(
            'search.update_search_text',
            [$photo->entityType(), $photo->entityId(), $searchable],
            true
        );
    }

    /**
     * @param User  $context
     * @param mixed $files
     *
     * @return void
     */
    private function checkPhotoQuota(User $context, mixed $files): void
    {
        if (!is_array($files)) {
            return;
        }

        $totalPhotos = collect($files)->groupBy('type')->map(function ($item) {
            return count($item);
        })->get('photo', 0);

        app('quota')->checkQuotaControlWhenCreateItem($context, Photo::ENTITY_TYPE, $totalPhotos);
    }

    /**
     * @inheritDoc
     */
    public function cleanUpRelationData(Photo $photo): void
    {
        // Detach all categories
        $photo->categories()->sync([]);

        // Remove photo info
        $photo->photoInfo()->delete();

        // Delete all storage file
        app('storage')->deleteAll($photo->image_file_id);

        // Delete all tag friends related to this photo
        $taggedFriends = app('events')->dispatch('friend.get_owner_tag_friends', [$photo], true);

        collect($taggedFriends)->each(function (mixed $tagFriend) {
            if (!$tagFriend instanceof TagFriendModel) {
                return true;
            }

            app('events')->dispatch('friend.delete_tag_friend', [$tagFriend->entityId()], true);

            return true;
        });

        $deletingParams = [
            'is_cover'  => $photo->is_cover_photo,
            'is_avatar' => $photo->is_profile_photo,
        ];

        app('events')->dispatch('photo.after_delete_photo', [$photo, $deletingParams]);

        // Update album data
        if ($photo->album_id <= 0) {
            return;
        }

        $photo->loadMissing(['album']);
        $album = $photo->album()->first();
        if (!$album instanceof Album) {
            return;
        }

        $this->getAlbumRepository()->removeAvatarFromAlbum($album, $photo);
        $this->getAlbumRepository()->removeCoverFromAlbum($album, $photo);
    }

    /**
     * @param UploadedFile         $file
     * @param array<string, mixed> $params
     */
    protected function uploadRawFile(User $context, UploadedFile $file, array $params): StorageFile
    {
        return upload()->setUser($context)->uploadWithParams($file, $params);
    }
}
