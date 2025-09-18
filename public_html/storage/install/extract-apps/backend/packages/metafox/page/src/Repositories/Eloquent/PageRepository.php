<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Policies\CategoryPolicy;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Repositories\PageHistoryRepositoryInterface;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\Page\BlockedScope;
use MetaFox\Page\Support\Browse\Scopes\Page\SortScope;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewScope;
use MetaFox\Page\Support\Browse\Scopes\PageSimilar\WhenScope as WhenScopeSimilar;
use MetaFox\Page\Support\Facade\Page as PageSupportFacade;
use MetaFox\Page\Support\PageSupport;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as SortScopeSupport;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\UserRole;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomProfile;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Browse\Scopes\User\CustomFieldScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class PageRepository.
 * @method Page getModel()
 * @method Page find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PageRepository extends AbstractRepository implements PageRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return Page::class;
    }

    protected function categoryRepository(): PageCategoryRepositoryInterface
    {
        return resolve(PageCategoryRepositoryInterface::class);
    }

    public function memberRepository(): PageMemberRepositoryInterface
    {
        return resolve(PageMemberRepositoryInterface::class);
    }

    public function historyRepository(): PageHistoryRepositoryInterface
    {
        return resolve(PageHistoryRepositoryInterface::class);
    }

    public function inviteRepository(): PageInviteRepositoryInterface
    {
        return resolve(PageInviteRepositoryInterface::class);
    }

    public function integratedRepository(): IntegratedModuleRepositoryInterface
    {
        return resolve(IntegratedModuleRepositoryInterface::class);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return EloquentBuilder
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function buildQueryViewPages(User $context, User $owner, array $attributes): EloquentBuilder
    {
        $sort         = Arr::get($attributes, 'sort', SortScopeSupport::SORT_DEFAULT);
        $sortType     = Arr::get($attributes, 'sort_type', SortScopeSupport::SORT_TYPE_DEFAULT);
        $when         = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $view         = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $search       = Arr::get($attributes, 'q');
        $categoryId   = Arr::get($attributes, 'category_id');
        $isFeatured   = Arr::get($attributes, 'is_featured');
        $customFields = Arr::get($attributes, 'custom_fields');

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view);

        $blockedScope = new BlockedScope();
        $blockedScope->setContextId($context->entityId());
        $query = $this->getModel()->newQuery()
            ->with(['userEntity']);

        $query->addScope(new FeaturedScope($isFeatured));

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['name']));
        }

        if ($categoryId > 0) {
            if (!is_array($categoryId)) {
                $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
            }

            $categoryScope = new CategoryScope();
            $categoryScope->setCategories($categoryId);
            $query->addScope($categoryScope);
        }

        if ($owner->entityId() != $context->entityId()) {
            $query->where('pages.user_id', '=', $owner->entityId())
                ->where('pages.is_approved', 1);

            $viewScope->setIsViewProfile(true);
        }

        if (!$isFeatured) {
            $privacyScope = new PrivacyScope();
            $privacyScope->setUserId($context->entityId());
            $privacyScope->setHasUserBlock(true);

            $query->addScope($privacyScope);
        }

        if ($customFields) {
            $customFieldScope = new CustomFieldScope();
            $customFieldScope->setCustomFields($customFields);
            $customFieldScope->setCurrentTable($this->getModel()->getTable());
            $customFieldScope->setSectionType(CustomField::SECTION_TYPE_PAGE);

            $query = $query->addScope($customFieldScope);
        }

        return $query->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($blockedScope)
            ->addScope($viewScope);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function viewPages(User $context, User $owner, array $attributes): Paginator
    {
        $view      = $attributes['view'];
        $limit     = $attributes['limit'];
        $profileId = $attributes['user_id'];

        if ($profileId > 0 && $profileId == $context->entityId()) {
            $attributes['view'] = $view = Browse::VIEW_MY;
        }

        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature($limit);
        }

        if (Browse::VIEW_PENDING == $view) {
            if (Arr::get($attributes, 'user_id') == 0) {
                if ($context->isGuest() || !$context->hasPermissionTo('page.approve')) {
                    throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
                }
            }
        }
        $categoryId = Arr::get($attributes, 'category_id', 0);

        if ($categoryId > 0) {
            $category = resolve(PageCategoryRepositoryInterface::class)->find($categoryId);

            policy_authorize(CategoryPolicy::class, 'viewActive', $context, $category);
        }

        return $this->buildQueryViewPages($context, $owner, $attributes)
            ->with(['userEntity'])
            ->simplePaginate($limit, ['pages.*']);
    }

    public function viewPage(User $context, int $id): Page
    {
        $page = $this->find($id);

        policy_authorize(PagePolicy::class, 'view', $context, $page);

        $page->with(['type', 'category', 'userEntity', 'pageText']);

        return $page;
    }

    public function createPage(User $context, array $attributes): Page
    {
        policy_authorize(PagePolicy::class, 'create', $context);

        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => (int) $context->hasPermissionTo('page.auto_approved'),
        ]);

        /** @var Page $page */
        $page = parent::create($attributes);

        $this->memberRepository()->addPageMember($page, $context->entityId(), PageMember::ADMIN);

        $page->assignRole(UserRole::PAGE_USER);

        if (!empty($attributes['user_ids'])) {
            $this->inviteRepository()->inviteFriends($context, $page->entityId(), $attributes['user_ids']);
        }

        $imageCrop = Arr::get($attributes, 'image.base64');
        $image     = $imageCrop ? upload()->convertBase64ToUploadedFile($imageCrop) : null;

        if ($image instanceof UploadedFile) {
            $params = ['image' => $image, 'image_crop' => $imageCrop];

            $this->updateAvatar($context, $page->entityId(), $params);
        }

        $page->refresh();

        $page->with(['category', 'userEntity', 'pageText']);

        return $page;
    }

    public function updatePage(User $context, int $id, array $attributes): Page
    {
        $page = $this->find($id);
        policy_authorize(PagePolicy::class, 'update', $context, $page);

        if (Arr::has($attributes, 'name')) {
            $paramsHistory = array_merge($attributes, [
                'type' => PageSupport::UPDATE_PAGE_NAME_TYPE,
            ]);

            $this->historyRepository()->createHistory($context, $page, $paramsHistory);
        }

        if (Arr::has($attributes, 'additional_information')) {
            CustomProfile::saveValues($page, $attributes['additional_information'], [
                'section_type' => CustomField::SECTION_TYPE_PAGE,
            ]);
        }

        $page->update($attributes);
        $page->refresh();

        if (Arr::has($attributes, 'landing_page')) {
            localCacheStore()->forget(PageSupportFacade::getCacheKeyDefaultTabActive($page));
        }

        return $page;
    }

    public function deletePage(User $context, int $id): bool
    {
        try {
            $page = $this->find($id);

            /*
             * Please move this dispatch to forceDelete when implementing soft delete if need
             */
            app('events')->dispatch('user.deleting', [$page]);

            $page->delete();

            /*
             * Please move this dispatch to forceDelete when implementing soft delete if need
             */
            app('events')->dispatch('user.deleted', [$page]);

            return true;
        } catch (\Throwable $error) {
            Log::channel('errorlog')->error('error delete page: ' . $error->getTraceAsString());
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function updateAvatar(User $context, int $id, array $attributes = []): array
    {
        $image            = Arr::get($attributes, 'image');
        $imageCrop        = Arr::get($attributes, 'image_crop');
        $tempFile         = Arr::get($attributes, 'temp_file') ?: 0;
        $photoId          = Arr::get($attributes, 'photo_id');
        $isEdittingAvatar = $image === null && $tempFile === 0 && $photoId === null;
        $page             = $this->find($id);

        if ($isEdittingAvatar && null == $page->avatar_file_id) {
            throw ValidationException::withMessages([
                __p('validation.required', ['attribute' => 'image']),
            ]);
        }

        if ($photoId) {
            $data = app('events')->dispatch('photo.make_parent_avatar', [$page, $photoId, $page, $imageCrop], true);

            return is_array($data)
                ? $data
                : [
                    'user'    => $page->refresh(),
                    'feed_id' => 0,
                ];
        }

        if (null != $image || $tempFile) {
            $params = [
                'privacy' => $page->privacy,
                'path'    => 'page',
                'files'   => [
                    [
                        'file'      => $image,
                        'temp_file' => $tempFile,
                    ],
                ],
            ];

            $photos = $this->createPhoto($context, $page, $params, 1, Page::PAGE_UPDATE_PROFILE_ENTITY_TYPE);

            if (empty($photos)) {
                abort(400, __('validation.something_went_wrong_please_try_again'));
            }

            $photos = $photos->toArray();

            $page->update([
                'avatar_id'      => $photos[0]['id'],
                'avatar_type'    => 'photo',
                'avatar_file_id' => $photos[0]['image_file_id'],
            ]);
        }

        $page->refresh();

        $uploadFile = upload()->convertBase64ToUploadedFile($imageCrop);

        $uploadedImage = upload()
            ->setThumbSizes(['50x50', '120x120', '200x200'])
            ->setPath('page')
            ->storeFile($uploadFile);

        $page->update(['avatar_file_id' => $uploadedImage->entityId()]);

        $feedId = 0;

        $itemId = $page->getAvatarId();

        $itemType = $page->getAvatarType();

        try {
            /** @var Content $feed */
            $feed   = app('events')->dispatch(
                'activity.get_feed_by_item_id',
                [$context, $itemId, $itemType, Page::PAGE_UPDATE_PROFILE_ENTITY_TYPE],
                true
            );
            $feedId = $feed->entityId();

            if (null == $image) {
                app('events')->dispatch('activity.push_feed_on_top', [$feedId], true);
            }
        } catch (Exception $e) {
            // Silent.
            Log::error($e->getMessage());
        }

        return [
            'user'       => $page->refresh(),
            'feed_id'    => $feedId,
            'is_pending' => false, //Todo check setting
        ];
    }

    public function updateCover(User $context, int $id, array $attributes): array
    {
        $page = $this->find($id);

        $coverData    = [];
        $positionData = [];
        $feedId       = 0;

        if (isset($attributes['position'])) {
            $positionData['cover_photo_position'] = $attributes['position'];
        }

        $image    = Arr::get($attributes, 'image');
        $tempFile = Arr::get($attributes, 'temp_file') ?: 0;

        if ($image || $tempFile) {
            $params = [
                'privacy'         => $page->privacy,
                'path'            => 'page',
                'thumbnail_sizes' => $page->getCoverSizes(),
                'files'           => [
                    [
                        'file'      => $image,
                        'temp_file' => $tempFile,
                    ],
                ],
            ];

            /** @var Collection $photos */
            $photos = $this->createPhoto($context, $page, $params, 2, Page::PAGE_UPDATE_COVER_ENTITY_TYPE);

            if (empty($photos)) {
                abort(400, __('validation.something_went_wrong_please_try_again'));
            }

            foreach ($photos as $photo) {
                $photo->toArray();
                $coverData = [
                    'cover_id'             => $photo['id'],
                    'cover_type'           => 'photo',
                    'cover_file_id'        => $photo['image_file_id'],
                    'cover_photo_position' => null,
                ];

                break;
            }
            unset($attributes['image']);
        }
        $page->update(array_merge($attributes, $coverData, $positionData));

        $page->refresh()->with('user');

        // $page->cover;//get photo -> feed
        $itemId   = $page->cover_id;
        $itemType = $page->cover_type;

        try {
            /** @var Content $feed */
            $feed = app('events')->dispatch(
                'activity.get_feed_by_item_id',
                [$context, $itemId, $itemType, Page::PAGE_UPDATE_COVER_ENTITY_TYPE],
                true
            );

            if ($feed instanceof Entity) {
                $feed->touch('created_at');

                $feedId = $feed->entityId();

                app('events')->dispatch('activity.push_feed_on_top', [$feedId], true);
            }
        } catch (Exception $e) {
            // Silent.
            Log::error($e->getMessage());
        }

        return [
            'user'       => $page,
            'feed_id'    => $feedId,
            'is_pending' => false, //Todo check setting
        ];
    }

    public function removeCover(User $context, int $id): bool
    {
        $page = $this->find($id);

        policy_authorize(PagePolicy::class, 'editCover', $context, $page);

        return $page->update($page->getCoverDataEmpty());
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_featured', 1)
            ->where('is_approved', 1)
            ->orderByDesc('featured_at')
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_sponsor', 1)
            ->where('is_approved', 1)
            ->simplePaginate($limit);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $params
     * @param int                  $type
     * @param string|null          $feedType
     *
     * @return Collection|null
     */
    protected function createPhoto(
        User $context,
        User $owner,
        array $params,
        int $type,
        ?string $feedType = null,
    ): ?Collection {
        /** @var Collection $photos */
        $photos = app('events')->dispatch('photo.create', [$context, $owner, $params, $type, $feedType], true);

        return $photos;
    }

    public function getPageForMention(User $context, array $attributes): Paginator
    {
        $search = $attributes['q'];
        $limit  = $attributes['limit'];

        $query = $this->getModel()->newQuery()
            ->join('page_members AS pm', function (JoinClause $join) use ($context) {
                $join->on('pm.page_id', '=', 'pages.id')
                    ->where('pm.user_id', '=', $context->entityId())
                    ->where('pages.is_approved', '=', 1);
            });

        if ('' != $search) {
            $query->orWhere('pages.name', $this->likeOperator(), $search . '%');
        }

        return $query->simplePaginate($limit, ['pages.*']);
    }

    public function getPageBuilder(User $user): Builder
    {
        $builder = DB::table('user_entities');

        $builder->select('user_entities.id')
            ->where('user_entities.entity_type', '=', Page::ENTITY_TYPE);

        $builder->join('pages', function (JoinClause $joinClause) {
            $joinClause->on('pages.id', '=', 'user_entities.id')
                ->where('pages.is_approved', '=', 1);
        });

        $builder->leftJoin('user_blocked as blocked_owner', function (JoinClause $join) use ($user) {
            $join->on('blocked_owner.owner_id', '=', 'user_entities.id')
                ->where('blocked_owner.user_id', '=', $user->entityId());
        })->whereNull('blocked_owner.owner_id');

        // Resources post by users blocked you.
        $builder->leftJoin('user_blocked as blocked_user', function (JoinClause $join) use ($user) {
            $join->on('blocked_user.user_id', '=', 'user_entities.id')
                ->where('blocked_user.owner_id', '=', $user->entityId());
        })->whereNull('blocked_user.user_id');

        return $builder;
    }

    /**
     * @throws AuthorizationException
     */
    public function viewSimilar(User $context, array $attributes): Paginator
    {
        policy_authorize(PagePolicy::class, 'viewAny', $context);

        $categoryId = Arr::get($attributes, 'category_id');
        $when       = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $limit      = Arr::get($attributes, 'limit', 3);
        $pageId     = Arr::get($attributes, 'page_id');

        // friend pages
        $friendPagesQuery = $this->buildQueryViewPages($context, $context, [
            'view' => ViewScope::VIEW_FRIEND_MEMBER,
        ]);

        $likePageIds = $this->memberRepository()->getModel()->newQuery()
            ->where('user_id', $context->entityId())
            ->pluck('page_id')->toArray();

        if (isset($pageId)) {
            $page        = $this->find($pageId);
            $categoryId  = $page->category_id;
            $likePageIds = in_array($pageId, $likePageIds) ? $likePageIds : array_merge($likePageIds, [(int) $pageId]);
        }

        if (!empty($likePageIds)) {
            $friendPagesQuery->whereKeyNot($likePageIds);
        }

        $whenScope = new WhenScopeSimilar();
        $whenScope->setTable('page_members')->setWhen($when);

        if (isset($categoryId)) {
            $friendPagesQuery->orderByRaw('CASE pages.category_id WHEN ? THEN 1 ELSE 0 END ' . Browse::SORT_TYPE_DESC, [
                $categoryId,
            ]);
        }

        return $friendPagesQuery
            ->select('pages.*')
            ->addScope($whenScope)
            ->simplePaginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function getProfileMenus(int $userId): array
    {
        $menus = $this->integratedRepository()->getModules($userId);

        return $menus->map(function ($menu) {
            return [
                'label' => __p($menu['label']),
                'value' => $menu['tab'],
            ];
        })->toArray();
    }

    public function getPageToPost(User $context, array $params): array
    {
        $privacy   = Arr::get($params, 'privacy');
        $limit     = Arr::get($params, 'limit', 10);
        $search    = Arr::get($params, 'q');
        $query     = $this->getModel()->newQuery();
        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView(ViewScope::VIEW_LIKED);
        $query->addScope($viewScope);
        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['name']));
        }
        if ($privacy) {
            $privacy = resolve(UserPrivacyRepositoryInterface::class)->convertPrivacySettingName($privacy);
            $query->leftJoin('user_privacy_values as upv', function (JoinClause $joinClause) use ($privacy) {
                $joinClause->on('upv.user_id', '=', 'pages.id')
                    ->where('upv.name', $privacy);
            })->leftJoin('core_privacy_members as cpm', function (JoinClause $joinClause) {
                $joinClause->on('cpm.privacy_id', '=', 'upv.privacy_id');
            })->where(function ($query) use ($context) {
                $query->whereNull('upv.name')
                    ->orWhere('cpm.user_id', $context->entityId());
            });
        }
        $query->select('pages.*')->limit($limit);
        $result = [];
        foreach ($query->get() as $item) {
            $result[] = [
                'label'         => $item->toTitle(),
                'value'         => $item->entityId(),
                'id'            => $item->entityId(),
                'name'          => $item->toTitle(),
                'module_name'   => 'page',
                'resource_name' => $item->entityType(),
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function handleSendInviteNotification(Page $page): void
    {
        $invites = $page->invites;
        foreach ($invites as $invite) {
            if ($invite instanceof PageInvite) {
                Notification::send(...$invite->toNotification());
            }
        }
    }

    /**
     * @param User  $context
     * @param int   $id
     * @param array $attributes
     *
     * @return void
     * @throws AuthorizationException
     */
    public function updateProfile(User $context, int $id, array $attributes): void
    {
        $page = $this->find($id);

        policy_authorize(PagePolicy::class, 'update', $context, $page);

        if (empty($attributes)) {
            return;
        }

        if (Arr::has($attributes, 'additional_information')) {
            CustomProfile::saveValues($page, $attributes['additional_information'], [
                'section_type' => CustomField::SECTION_TYPE_PAGE,
            ]);
        }
    }
}
