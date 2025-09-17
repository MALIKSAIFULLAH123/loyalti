<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Policies\CategoryPolicy;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Repositories\GroupChangePrivacyRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Group\BlockedScope;
use MetaFox\Group\Support\Browse\Scopes\Group\PrivacyScope;
use MetaFox\Group\Support\Browse\Scopes\Group\SortScope;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewScope;
use MetaFox\Group\Support\Browse\Scopes\GroupSimilar\WhenScope as WhenScopeSimilar;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasApprove as HasApproveContract;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasSponsor as HasSponsorContract;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as SortScopeSupport;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomProfile;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Browse\Scopes\User\CustomFieldScope;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Traits\UserMorphTrait;
use Throwable;

/**
 * Class GroupRepository.
 * @method Group getModel()
 * @method Group find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @inore
 */
class GroupRepository extends AbstractRepository implements GroupRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use CollectTotalItemStatTrait;
    use HasSponsorInFeed;
    use UserMorphTrait;

    public function model(): string
    {
        return Group::class;
    }

    /**
     * @return PrivacyTypeHandler
     */
    private function getPrivacyTypeHandler(): PrivacyTypeHandler
    {
        return resolve(PrivacyTypeHandler::class);
    }

    /**
     * @return InviteRepositoryInterface
     */
    private function groupInviteRepository(): InviteRepositoryInterface
    {
        return resolve(InviteRepositoryInterface::class);
    }

    /**
     * @return IntegratedModuleRepositoryInterface
     */
    private function integratedRepository(): IntegratedModuleRepositoryInterface
    {
        return resolve(IntegratedModuleRepositoryInterface::class);
    }

    /**
     * @return GroupChangePrivacyRepositoryInterface
     */
    private function changePrivacyRepository(): GroupChangePrivacyRepositoryInterface
    {
        return resolve(GroupChangePrivacyRepositoryInterface::class);
    }

    /**
     * @return CategoryRepositoryInterface
     */
    private function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function viewGroups(User $context, User $owner, array $attributes): Paginator
    {
        $view      = Arr::get($attributes, 'view');
        $limit     = Arr::get($attributes, 'limit');
        $profileId = Arr::get($attributes, 'user_id');
        $sortType  = Arr::get($attributes, 'sort_type', SortScopeSupport::SORT_TYPE_DEFAULT);
        $sort      = Arr::get($attributes, 'sort', SortScopeSupport::SORT_DEFAULT);

        app('events')->dispatch('group.view_groups.support_check_view_permissions', [$context, $owner, $attributes]);

        switch ($view) {
            case Browse::VIEW_FEATURE:
                return $this->findFeature($limit);
            case Browse::VIEW_PENDING:
                if ($profileId != 0 && $profileId == $context->entityId()) {
                    break;
                }

                if ($context->isGuest() || !$context->hasPermissionTo('group.approve')) {
                    throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
                }

                break;
        }

        if ($context->entityId() && $profileId == $context->entityId() && !in_array($view, [Browse::VIEW_PENDING, ViewScope::VIEW_PROFILE])) {
            $attributes['view'] = Browse::VIEW_MY;
        }

        $query = $this->buildQueryViewGroups($context, $owner, $attributes);

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        return $query->with(['userEntity'])
            ->addScope($sortScope)
            ->simplePaginate($limit, ['groups.*']);
    }

    private function buildQueryViewGroups(User $context, User $owner, array $attributes): EloquentBuilder
    {
        $when         = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $view         = Arr::get($attributes, 'view');
        $search       = Arr::get($attributes, 'q');
        $profileId    = Arr::get($attributes, 'user_id', 0);
        $isFeatured   = Arr::get($attributes, 'is_featured');
        $categoryId   = Arr::get($attributes, 'category_id', 0);
        $isJoined     = Arr::get($attributes, 'is_joined', true);
        $customFields = Arr::get($attributes, 'custom_fields');

        if ($categoryId > 0) {
            $category = $this->categoryRepository()->find($categoryId);

            policy_authorize(CategoryPolicy::class, 'viewActive', $context, $category);
        }

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view)
            ->setProfileId($profileId)->setIsJoined($isJoined);

        $blockedScope = new BlockedScope();
        $blockedScope->setContextId($context->entityId());

        $query = $this->getModel()->newQuery();

        $query->addScope(new FeaturedScope($isFeatured));

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['name']));
        }

        if (!$context->hasPermissionTo('group.moderate') && !$isFeatured) {
            // Scopes.
            $privacyScope = new PrivacyScope();
            $privacyScope
                ->setUserId($context->entityId())
                ->setView($view);

            $privacyScope->setHasUserBlock(true);

            $query = $query->addScope($privacyScope);
        }

        if ($categoryId > 0) {
            if (!is_array($categoryId)) {
                $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
            }

            $categoryScope = new CategoryScope();
            $categoryScope->setCategories($categoryId);
            $query->addScope($categoryScope);
        }

        if ($owner->entityId() != $context->entityId() && ViewScope::VIEW_PROFILE != $view) {
            $query->where('groups.user_id', '=', $owner->entityId())
                ->where('groups.is_approved', HasApproveContract::IS_APPROVED);

            $viewScope->setIsViewProfile(true);
        }

        if (isset($attributes['privacy_type'])) {
            $query->where('groups.privacy_type', '=', $attributes['privacy_type']);
        }

        if (isset($attributes['not_in_ids']) && !empty($attributes['not_in_ids'])) {
            $query->whereNotIn('groups.id', $attributes['not_in_ids']);
        }

        if ($customFields) {
            $customFieldScope = new CustomFieldScope();
            $customFieldScope->setCustomFields($customFields);
            $customFieldScope->setCurrentTable($this->getModel()->getTable());
            $customFieldScope->setSectionType(CustomField::SECTION_TYPE_GROUP);

            $query = $query->addScope($customFieldScope);
        }

        return $query->addScope($whenScope)
            ->addScope($viewScope)
            ->addScope($blockedScope);
    }

    public function getGroup(int $id): Group
    {
        return $this->with(['user', 'category', 'groupText'])
            ->find($id);
    }

    public function viewGroup(User $context, int $id, ?GroupInviteCode $inviteCode): Group
    {
        $group = $this->getGroup($id);
        $code  = null;
        if (!empty($inviteCode)) {
            $code = $inviteCode->code;
        }

        policy_authorize(GroupPolicy::class, 'view', $context, $group, $code);

        return $group;
    }

    public function createGroup(User $context, User $owner, array $attributes): Group
    {
        policy_authorize(GroupPolicy::class, 'create', $context);

        $attributes = array_merge($attributes, [
            'user_id'              => $context->entityId(),
            'user_type'            => $context->entityType(),
            'privacy'              => $this->getPrivacyTypeHandler()->getPrivacy($attributes['privacy_type']),
            'privacy_item'         => $this->getPrivacyTypeHandler()->getPrivacyItem($attributes['privacy_type']),
            'is_rule_confirmation' => false,
        ]);

        //only apply auto approve when $context == $owner
        if ($context->entityId() == $owner->entityId()) {
            if (!$context->hasPermissionTo('group.auto_approved')) {
                $attributes['is_approved'] = 0;
            }
        }

        /** @var Group $group */
        $group = parent::create($attributes);

        if (!empty($attributes['user_ids'])) {
            $this->groupInviteRepository()->inviteFriends($context, $group->entityId(), $attributes['user_ids']);
        }

        $group->refresh();

        return $group;
    }

    public function updateGroup(User $context, int $id, array $attributes): Group
    {
        $group = $this->find($id);

        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        if (Arr::has($attributes, 'privacy_type') && $attributes['privacy_type'] != $group->getPrivacyType()) {
            if ($group->isClosedPrivacy() && PrivacyTypeHandler::PUBLIC == $attributes['privacy_type'] || $group->isSecretPrivacy()) {
                abort(403, __p('group::phrase.change_privacy_group_error'));
            }

            $result = $this->changePrivacyRepository()->createRequest($group, $context, $attributes);
            if (!$result) {
                abort(403, __p('group::phrase.request_change_privacy_group_exists'));
            }
            unset($attributes['privacy_type']);
        }

        $group->update($attributes);

        if (Arr::has($attributes, 'landing_page')) {
            localCacheStore()->forget(GroupFacade::getCacheKeyDefaultTabActive($group));
        }

        $group->refresh();

        return $group;
    }

    public function deleteGroup(User $context, int $id): bool
    {
        try {
            $group = $this->find($id);

            /*
             * Please move this dispatch to forceDelete when implementing soft delete if need
             */
            app('events')->dispatch('user.deleting', [$group]);

            $group->delete();

            /*
             * Please move this dispatch to forceDelete when implementing soft delete if need
             */
            app('events')->dispatch('user.deleted', [$group]);

            return true;
        } catch (Throwable $error) {
            Log::channel('errorlog')->error('error delete group: ' . $error->getTraceAsString());
        }

        return false;
    }

    public function updateAvatar(User $context, int $id, string $imageBase46): bool
    {
        $group = $this->find($id);

        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        $image = upload()->convertBase64ToUploadedFile($imageBase46);

        $params = [
            'privacy' => $group->privacy,
            'path'    => 'group',
            'files'   => [
                [
                    'file' => $image,
                ],
            ],
        ];

        /** @var Collection $photos */
        $photos = app('events')->dispatch('photo.create', [$context, $group, $params, 1], true);

        $photos = $photos->toArray();
        $group->update([
            'avatar_id'      => $photos[0]['id'],
            'avatar_type'    => 'photo',
            'avatar_file_id' => $photos[0]['image_file_id'],
        ]);

        return true;
    }

    public function updateCover(User $context, int $id, array $attributes): array
    {
        $group = $this->find($id);

        $positionData = $coverData = [];

        $feedId = 0;

        if (isset($attributes['position'])) {
            $positionData['cover_photo_position'] = $attributes['position'];
        }

        $image    = Arr::get($attributes, 'image');
        $tempFile = Arr::get($attributes, 'temp_file') ?: 0;

        if ($image || $tempFile) {
            $params = [
                'privacy'         => $group->privacy,
                'path'            => 'group',
                'thumbnail_sizes' => $group->getCoverSizes(),
                'files'           => [
                    [
                        'file'      => $image,
                        'temp_file' => $tempFile,
                    ],
                ],
            ];

            /** @var Collection $photos */
            $photos = app('events')->dispatch(
                'photo.create',
                [$context, $group, $params, 2, Group::GROUP_UPDATE_COVER_ENTITY_TYPE],
                true
            );

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

        $group->update(array_merge($attributes, $coverData, $positionData));

        $group->refresh()->with('user');

        // $group->cover;//get photo -> feed
        $itemId   = $group->cover_id;
        $itemType = $group->cover_type;

        try {
            /** @var Content $feed */
            $feed = app('events')->dispatch(
                'activity.get_feed_by_item_id',
                [$context, $itemId, $itemType, Group::GROUP_UPDATE_COVER_ENTITY_TYPE],
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
            'user'       => $group,
            'feed_id'    => $feedId,
            'is_pending' => false, //Todo check setting
        ];
    }

    public function removeCover(User $context, int $id): bool
    {
        $group = $this->find($id);

        policy_authorize(GroupPolicy::class, 'editCover', $context, $group);

        return $group->update($group->getCoverDataEmpty());
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_featured', HasFeature::IS_FEATURED)
            ->where('is_approved', HasApproveContract::IS_APPROVED)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_sponsor', HasSponsorContract::IS_SPONSOR)
            ->where('is_approved', HasApproveContract::IS_APPROVED)
            ->simplePaginate($limit);
    }

    public function getGroupForMention(User $context, array $attributes): Paginator
    {
        $search = $attributes['q'];
        $limit  = $attributes['limit'];

        $query = $this->getModel()->newQuery()
            ->join('group_members AS gm', function (JoinClause $join) use ($context) {
                $join->on('gm.group_id', '=', 'groups.id')
                    ->where('gm.user_id', $context->entityId())
                    ->where('groups.is_approved', HasApproveContract::IS_APPROVED)
                    ->where('groups.privacy_type', PrivacyTypeHandler::PUBLIC);
            });

        if ('' != $search) {
            $query->orWhere('groups.name', $this->likeOperator(), $search . '%');
        }

        return $query->simplePaginate($limit, ['groups.*']);
    }

    public function updatePendingMode(User $context, int $id, int $pendingMode): bool
    {
        $group = $this->find($id);
        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $group);

        return $group->update(['pending_mode' => $pendingMode]);
    }

    public function hasGroupRule(Group $group): bool
    {
        return $group->total_rule > 0;
    }

    public function hasGroupRuleConfirmation(Group $group): bool
    {
        return $this->hasGroupRule($group) && $group->is_rule_confirmation;
    }

    public function hasGroupQuestionsConfirmation(Group $group): bool
    {
        return $this->hasGroupQuestions($group) && $group->is_answer_membership_question;
    }

    public function hasGroupQuestions(Group $group): bool
    {
        return $group->total_question > 0;
    }

    public function hasMembershipQuestion(Group $group): bool
    {
        return $group->total_question > 0 || $group->total_rule > 0;
    }

    public function updateRuleConfirmation(User $context, int $id, bool $isConfirmation): Group
    {
        $group = $this->find($id);

        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $group);

        return $this->update(['is_rule_confirmation' => $isConfirmation], $id);
    }

    public function updateAnswerMembershipQuestion(User $context, int $id, bool $isConfirmation): Group
    {
        $group = $this->find($id);

        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        return $this->update(['is_answer_membership_question' => $isConfirmation], $id);
    }

    public function getPublicGroupBuilder(User $user): Builder
    {
        return DB::table('user_entities')
            ->select('user_entities.id')
            ->join('groups', function (JoinClause $joinClause) {
                $joinClause->on('groups.id', '=', 'user_entities.id')
                    ->where('groups.is_approved', '=', 1);
            })
            ->leftJoin('user_blocked as blocked_owner', function (JoinClause $join) use ($user) {
                $join->on('blocked_owner.owner_id', '=', 'user_entities.id')
                    ->where('blocked_owner.user_id', '=', $user->entityId());
            })
            ->leftJoin('user_blocked as blocked_user', function (JoinClause $join) use ($user) {
                $join->on('blocked_user.user_id', '=', 'user_entities.id')
                    ->where('blocked_user.owner_id', '=', $user->entityId());
            })
            ->whereNull('blocked_owner.owner_id')
            ->whereNull('blocked_user.user_id');
    }

    public function getGroupBuilder(User $user): Builder
    {
        return DB::table('user_entities')
            ->select('user_entities.id')
            ->join('groups', function (JoinClause $joinClause) {
                $joinClause->on('groups.id', '=', 'user_entities.id')
                    ->where('groups.is_approved', '=', 1);
            })
            ->leftJoin('user_blocked as blocked_owner', function (JoinClause $join) use ($user) {
                $join->on('blocked_owner.owner_id', '=', 'user_entities.id')
                    ->where('blocked_owner.user_id', '=', $user->entityId());
            })
            ->leftJoin('user_blocked as blocked_user', function (JoinClause $join) use ($user) {
                $join->on('blocked_user.user_id', '=', 'user_entities.id')
                    ->where('blocked_user.owner_id', '=', $user->entityId());
            })
            ->leftJoin('group_members', function (JoinClause $joinClause) use ($user) {
                $joinClause->on('group_members.group_id', '=', 'groups.id')
                    ->where('group_members.user_id', '=', $user->entityId());
            })
            ->where(function (Builder $builder) {
                $builder->where('groups.privacy_type', '=', PrivacyTypeHandler::PUBLIC)
                    ->orWhere(function (Builder $builder) {
                        $builder->where('groups.privacy_type', '=', PrivacyTypeHandler::CLOSED)
                            ->whereNotNull('group_members.id');
                    });
            })
            ->whereNull('blocked_owner.owner_id')
            ->whereNull('blocked_user.user_id');
    }

    public function toPendingNotifiables(Group $group, User $context): array
    {
        $admins = $group->admins()
            ->with(['user'])
            ->get()
            ->map(function ($admin) {
                return $admin->user;
            });

        $notifiables = collect($admins);

        if (UserValue::getUserValueSettingByName($group, 'approve_or_deny_post')) {
            $moderators = $group->moderators()
                ->with(['user'])
                ->get()
                ->map(function ($moderator) {
                    return $moderator->user;
                });

            foreach ($moderators as $moderator) {
                $notifiables->push($moderator);
            }
        }

        return $notifiables
            ->unique('id')
            ->filter(function ($notifiable) use ($context) {
                return $notifiable->entityId() != $context->entityId();
            })
            ->all();
    }

    public function hasDeleteFeedPermission(User $context, Content $resource, Group $group): bool
    {
        return $context->entityId() == $resource->userId();
    }

    /**
     * @inheritDoc
     */
    public function handleSendInviteNotification(Group $group): void
    {
        $invites = $group->invites;
        foreach ($invites as $invite) {
            if ($invite instanceof Invite) {
                Notification::send(...$invite->toNotification());
            }
        }
    }

    public function getProfileMenus(int $groupId): array
    {
        $menus = $this->integratedRepository()->getModulesActive($groupId);

        return $menus->map(function ($menu) {
            return [
                'label' => __p($menu['label']),
                'value' => $menu['name'],
            ];
        })->toArray();
    }

    public function getGroupToPost(User $context, array $params): array
    {
        $privacy   = Arr::get($params, 'privacy');
        $limit     = Arr::get($params, 'limit', 10);
        $search    = Arr::get($params, 'q');
        $query     = $this->getModel()->newQuery();
        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView(ViewScope::VIEW_JOINED);
        $query->addScope($viewScope);
        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['name']));
        }
        if ($privacy) {
            $privacy = resolve(UserPrivacyRepositoryInterface::class)->convertPrivacySettingName($privacy);
            $query->leftJoin('user_privacy_values as upv', function (JoinClause $joinClause) use ($privacy) {
                $joinClause->on('upv.user_id', '=', 'groups.id')
                    ->where('upv.name', $privacy);
            })->leftJoin('core_privacy_members as cpm', function (JoinClause $joinClause) {
                $joinClause->on('cpm.privacy_id', '=', 'upv.privacy_id');
            })->where(function ($query) use ($context) {
                $query->whereNull('upv.name')
                    ->orWhere('cpm.user_id', $context->entityId());
            });
        }
        $query->select('groups.*')->limit($limit);
        $result = [];
        foreach ($query->get() as $item) {
            $result[] = [
                'label'         => $item->toTitle(),
                'value'         => $item->entityId(),
                'id'            => $item->entityId(),
                'name'          => $item->toTitle(),
                'module_name'   => 'group',
                'resource_name' => $item->entityType(),
            ];
        }

        return $result;
    }

    /**
     * @throws AuthorizationException
     */
    public function viewSimilar(User $context, array $attributes): Paginator
    {
        policy_authorize(GroupPolicy::class, 'viewAny', $context);

        $categoryId = Arr::get($attributes, 'category_id');
        $when       = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $limit      = Arr::get($attributes, 'limit', 3);
        $groupId    = Arr::get($attributes, 'group_id');
        $query      = $this->getModel()->newQuery();
        $contextId  = $context->entityId();

        if (isset($groupId)) {
            $group      = $this->find($groupId);
            $categoryId = $group->category_id;

            $query->whereNot('groups.id', $groupId);
        }

        $whenScope = new WhenScopeSimilar();
        $whenScope->setTable('group_members')->setWhen($when);

        $query->where(function (EloquentBuilder $builder) use ($categoryId, $contextId) {
            /*Groups of friends*/
            $builder->orWhereIn('groups.id', function ($query) use ($contextId) {
                $query->select('group_id')
                    ->from('group_members')
                    ->join('friends AS f', function (JoinClause $join) use ($contextId) {
                        $join->on('f.user_id', '=', 'group_members.user_id')
                            ->where('f.owner_id', '=', $contextId)
                            ->whereNot('group_members.user_id', $contextId);
                    })->groupBy('group_id');
            });

            /*Groups that belong to the same category*/
            $builder->orWhereIn('groups.id', function ($query) use ($categoryId) {
                $query->select('groups.id')->from('groups')->where('groups.category_id', $categoryId);
            });

            /*Group with the most members*/
            $builder->orWhereIn('groups.id', function ($query) {
                $query->select('groups.id')->from('groups')->orderByDesc('groups.total_member');
            });
        });

        $query->leftJoin('group_members AS gm', function (JoinClause $join) use ($contextId) {
            $join->on('gm.group_id', '=', 'groups.id')
                ->where('gm.user_id', '=', $contextId);
        });

        $query->whereNull('gm.group_id')
            ->where('groups.privacy_type', '=', PrivacyTypeHandler::PUBLIC);

        $isCategory = DB::raw("CASE WHEN groups.category_id = $categoryId THEN 0 ELSE 1 END as is_category");
        $isFriend   = DB::raw("CASE WHEN groups.id IN ( SELECT group_id
                                                          FROM group_members  AS gm
                                                          JOIN friends AS f
                                                          ON gm.user_id = f.owner_id
                                                          AND f.user_id = $contextId
                                                          GROUP BY group_id
                                                        ) THEN 0 ELSE 1 END as is_friend");

        $blockedScope = new BlockedScope();
        $blockedScope->setContextId($context->entityId());

        $privacyScope = new PrivacyScope();
        $privacyScope->setUserId($context->entityId());

        $privacyScope->setHasUserBlock(true);

        $query->where('groups.is_approved', MetaFoxConstant::IS_ACTIVE);

        $query->select('groups.*', $isFriend, $isCategory)
            ->addScope($whenScope)
            ->addScope($blockedScope)
            ->addScope($privacyScope)
            ->orderByRaw('is_friend, is_category')
            ->orderByDesc('groups.total_member');

        return $query
            ->simplePaginate($limit);
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
        $group = $this->find($id);

        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        if (empty($attributes)) {
            return;
        }

        if (Arr::has($attributes, 'additional_information')) {
            CustomProfile::saveValues($group, $attributes['additional_information'], [
                'section_type' => CustomField::SECTION_TYPE_GROUP,
            ]);
        }
    }
}
