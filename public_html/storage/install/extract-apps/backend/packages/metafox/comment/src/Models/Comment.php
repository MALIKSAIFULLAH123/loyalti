<?php

namespace MetaFox\Comment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection as CollectionAbstract;
use Illuminate\Support\Facades\Auth;
use MetaFox\Comment\Database\Factories\CommentFactory;
use MetaFox\Comment\Notifications\CommentApproveNotification;
use MetaFox\Comment\Notifications\CommentNotification;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasTaggedFriendTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserEntity as FacadeUserEntity;

/**
 * Class Comment.
 *
 * @property        int                    $id
 * @property        int                    $parent_id
 * @property        int                    $owner_id               - user id of content
 * @property        int                    $owner_type
 * @property        int                    $user_id
 * @property        string                 $user_type
 * @property        int                    $item_id
 * @property        string                 $item_type
 * @property        bool                   $is_spam
 * @property        string                 $text
 * @property        string                 $created_at
 * @property        string                 $text_parsed
 * @property        string                 $updated_at
 * @property        User                   $user
 * @property        User                   $owner
 * @property        Content                $item
 * @property        Comment                $parentComment
 * @property        Collection             $children
 * @property        Collection             $commentHides
 * @property        CommentAttachment|null $commentAttachment
 * @property        mixed                  $tagged_user_ids
 * @property        bool                   $is_edited
 * @property        bool                   $is_hidden
 * @property        int                    $total_tag_friend
 * @method   static CommentFactory         factory(...$parameters)
 * @mixin Builder
 * @method mixed incrementOrDecrement($column, $amount, $extra, $method)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Comment extends Model implements
    ActionEntity,
    Content,
    HasApprove,
    HasTotalComment,
    HasTotalLike,
    \MetaFox\Platform\Contracts\HasItemMorph,
    HasTaggedFriend,
    HasHashTag,
    IsNotifyInterface
{
    use HasContent;
    use HasOwnerMorph;
    use HasUserMorph;
    use HasItemMorph;
    use HasFactory;
    use HasTaggedFriendTrait;

    public const ENTITY_TYPE = 'comment';

    /**
     * @var array<string, mixed>
     */
    protected $casts = [
        'is_approved'     => 'boolean',
        'is_spam'         => 'boolean',
        'is_edited'       => 'boolean',
        'tagged_user_ids' => 'array',
    ];

    /** @var string[] */
    protected $fillable = [
        'owner_id',
        'owner_type',
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'parent_id',
        'is_approved',
        'text',
        'text_parsed',
        'total_comment',
        'total_pending_comment',
        'total_like',
        'is_edited',
        'is_spam',
        'tagged_user_ids',
    ];

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo($this, 'parent_id', 'id');
    }

    public function getTotalPendingUserReplyAttribute(): int
    {
        // reduce number of queries
        if (!$this->total_pending_reply) {
            return 0;
        }

        $userId = Auth::user()?->entityId();

        if (!$userId) {
            return 0;
        }

        return $this->where([
            'user_id'     => $userId,
            'parent_id'   => $this->id,
            'is_approved' => 0,
        ])->count();
    }

    /**
     * Add this method to support Factories.
     * @return HasMany
     */
    public function replies()
    {
        return $this->hasMany($this, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        $context = user();

        $relation = $this->hasMany($this, 'parent_id', 'id');
        if ($context->hasPermissionTo('admincp.has_admin_access')) {
            return $relation;
        }

        return $relation
            ->where(function ($builder) use ($context) {
                $builder->where('is_approved', 1)
                    ->orWhere('user_id', $context->entityId());
            });
    }

    public function commentHides(): HasMany
    {
        return $this->hasMany(CommentHide::class, 'item_id', 'id');
    }

    public function commentAttachment(): HasOne
    {
        return $this->hasOne(CommentAttachment::class, 'comment_id', 'id');
    }

    public function commentHistory(): HasMany
    {
        return $this->HasMany(CommentHistory::class, 'comment_id', 'id');
    }

    public function toTitle(): string
    {
        return $this->item->toTitle();
    }

    public function toNotification(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        // @todo: refactor this method, to many condition branches
        $context = match (Auth::guest()) {
            true  => UserFacade::getGuestUser(),
            false => $this->user,
        };

        $isReply = $this->parent_id > 0;

        $notifiables = match ($isReply) {
            true  => $this->toReplyNotifiables($context),
            false => $this->toCommentNotifiables($context),
        };

        $notifiables = array_filter(array_unique($notifiables), function ($owner) {
            return $owner instanceof IsNotifiable;
        });

        if (!count($notifiables)) {
            return null;
        }

        return [$notifiables, new CommentNotification($this)];
    }

    public function toApprovedNotification(): array
    {
        return [$this->user, new CommentApproveNotification($this)];
    }

    protected function toCommentNotifiables(User $context): array
    {
        $owners     = [];
        $feedItem   = $this->item;
        $attributes = $taggedFriends = null;

        //check when the comment delete after an item deleting
        if (null !== $feedItem) {
            $taggedFriends = app('events')->dispatch('friend.get_owner_tag_friends', [$feedItem], true);

            $attributes = [
                'item_id'   => $feedItem->entityId(),
                'item_type' => $feedItem->entityType(),
            ];
        }

        $usersComment = null;
        $pass         = app('events')->dispatch('comment.owner.notification', [$this->owner, $this->item], true);

        if (is_array($attributes) && $this->userId() != $this->ownerId()) {
            if ($pass || null === $pass) {
                $owners = array_merge($owners, $this->getOwnerNofitiables($context));
            }

            $usersComment = app('events')->dispatch('comment.get_user_by_item', [$context, $attributes], true);
        }

        if ($taggedFriends instanceof CollectionAbstract) {
            foreach ($taggedFriends as $taggedFriend) {
                $taggedOwner = $taggedFriend->owner;
                if (empty($taggedOwner)) {
                    continue;
                }

                if ($context->entityId() == $taggedOwner->entityId()) {
                    continue;
                }
                $taggedFriendPass = app('events')->dispatch(
                    'comment.owner.notification',
                    [$taggedFriend->owner, $this->item],
                    true
                );

                if ($taggedFriendPass === false) {
                    continue;
                }

                if ($taggedOwner instanceof HasPrivacyMember) {
                    $notifiables = app('events')->dispatch(
                        'friend.mention.notifiables',
                        [$context, $taggedOwner],
                        true
                    );

                    if (is_array($notifiables) && count($notifiables)) {
                        $owners = array_merge($owners, $notifiables);
                    }

                    continue;
                }

                $owners[] = $taggedOwner;
            }
        }

        if ($usersComment instanceof CollectionAbstract) {
            foreach ($usersComment as $user) {
                if (!$user instanceof UserEntity) {
                    continue;
                }

                if ($user->entityId() == $context->entityId()) {
                    continue;
                }

                if (!$pass && $user->entityId() == $this->owner->entityId()) {
                    continue;
                }
                $userPass = app('events')->dispatch('comment.owner.notification', [$user->detail, $this->item], true);

                if ($userPass === false) {
                    continue;
                }
                $owners = array_merge($owners, [$user->detail]);
            }
        }

        $taggedUsers = $this->tagged_user_ids;

        if (is_array($taggedUsers)) {
            foreach ($taggedUsers as $id) {
                $owner = FacadeUserEntity::getById($id)->detail;

                if ($owner->entityId() == $this->owner->entityId()) {
                    $owners = array_diff($owners, [$owner]);
                }
            }
        }

        return $owners;
    }

    protected function toReplyNotifiables(User $context): array
    {
        if (null === $this->parentComment) {
            return [];
        }

        $notifiables = [];

        $taggedUserIds = $this->tagged_user_ids;

        if (is_string($taggedUserIds)) {
            $taggedUserIds = json_decode($taggedUserIds, true);
        }

        if (!is_array($taggedUserIds)) {
            $taggedUserIds = [];
        }

        if ($this->userId() != $this->parentComment->userId()) {
            /*
             * In case parent user is mention, does not need to send this notification
             */
            if (!in_array($this->parentComment->userId(), $taggedUserIds)) {
                $notifiables[] = $this->parentComment->user;
            }
        }

        if ($this->userId() != $this->ownerId()) {
            $owners = $this->getOwnerNofitiables($context);

            foreach ($owners as $owner) {
                if ($owner->entityId() == $this->parentComment->userId()) {
                    continue;
                }

                /*
                 * In case owner of feed is mentioned on this comment, does not need to send this notification
                 */
                if (!in_array($owner->entityId(), $taggedUserIds)) {
                    $notifiables[] = $owner;
                }
            }
        }

        return $notifiables;
    }

    protected function getOwnerNofitiables(User $context): array
    {
        if (!$this->owner instanceof HasPrivacyMember) {
            return [$this->owner];
        }

        $notifiables = app('events')->dispatch(
            'friend.mention.notifiables',
            [$context, $this->owner],
            true
        );

        if (!is_array($notifiables)) {
            return [];
        }

        return $notifiables;
    }

    public function toLink(): ?string
    {
        $item = $this->item;

        if ($item instanceof self) {
            $item = $item->item;
        }

        if (null === $item) {
            return null;
        }

        if (method_exists($item, 'toDetailLink')) {
            return $item->toDetailLink() . '?' . http_build_query(['comment_id' => $this->entityId()]);
        }

        if (!$item instanceof HasUrl) {
            return null;
        }

        return $item->toLink() . '?' . http_build_query(['comment_id' => $this->entityId()]);
    }

    public function toFullLink(): ?string
    {
        return !$this->toLink() ?: url_utility()->makeApiFullUrl($this->toLink());
    }

    public function toUrl(): ?string
    {
        $item = $this->item;

        if ($item instanceof self) {
            $item = $item->item;
        }

        if (null === $item) {
            return null;
        }

        if (method_exists($item, 'toDetailUrl')) {
            return $item->toDetailUrl() . '?' . http_build_query(['comment_id' => $this->entityId()]);
        }

        if (!$item instanceof HasUrl) {
            return null;
        }

        return $item->toUrl() . '?' . http_build_query(['comment_id' => $this->entityId()]);
    }

    public function toRouter(): ?string
    {
        $item = $this->item;

        if ($item instanceof self) {
            $item = $item->item;
        }

        if (null === $item) {
            return null;
        }

        $isReply = $this->parent_id > 0 ? 1 : 0;

        if ($item->keep_comment_item_router) {
            return $item->toRouter() . '?' . http_build_query([
                'comment_id' => $this->entityId(),
                'is_reply'   => $isReply,
            ]);
        }

        $packageAlias = PackageManager::getAliasForEntityType($item->entityType());

        if (!$packageAlias) {
            return null;
        }

        $queryParams = http_build_query([
            'item_id'       => $item->entityId(),
            'resource_name' => $item->entityType(),
            'module_name'   => $packageAlias,
            'is_reply'      => $isReply,
        ]);

        return url_utility()->makeApiMobileResourceUrl($this->entityType(), $this->entityId()) . '?' . $queryParams;
    }

    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'comment_tag_data',
            'item_id',
            'tag_id'
        )->using(CommentTagData::class);
    }

    public function toMail(MailMessage $service, ?UserEntity $user, ?UserEntity $owner, bool $isMention = false): MailMessage
    {
        $friendName = $owner instanceof UserEntity ? $owner->name : null;
        $yourName   = $user instanceof UserEntity ? $user->name : null;

        $emailTitle = __p('comment::phrase.username_mentioned_you_in_a_comment_subject', [
            'username' => $yourName,
            'item'     => $this->entityType(),
        ]);

        $emailLine = __p('comment::phrase.hi_friend_username_mentioned_you_in_a_comment', [
            'friend'   => $friendName,
            'username' => $yourName,
            'item'     => $this->entityType(),
        ]);

        $url = $this->toUrl();

        return $service
            ->subject($emailTitle)
            ->line($emailLine)
            ->action(__p('core::phrase.review_now'), $url ?? '');
    }

    /**
     * @param  UserEntity $user
     * @param  UserEntity $owner
     * @param  bool       $isMention
     * @return string
     */
    public function toCallbackMessage(UserEntity $user, UserEntity $owner, bool $isMention = false): string
    {
        $yourName = $user->name;
        $owner    = $owner->detail;

        if ($owner instanceof HasPrivacyMember) {
            return __p('comment::notification.username_mentioned_entity_type_title_in_a_comment_review_now', [
                'username'     => $yourName,
                'entity_type'  => $owner->entityType(),
                'entity_title' => $owner->toTitle(),
                'is_review'    => $this->isReview(),
            ]);
        }

        return __p('comment::notification.username_mentioned_you_in_a_comment_review_now', [
            'username'  => $yourName,
            'is_review' => $this->isReview(),
        ]);
    }

    /**
     * @return string
     */
    public function toTagFriendUrl(): ?string
    {
        return $this->toUrl();
    }

    /**
     * @return string
     */
    public function toTagFriendLink(): ?string
    {
        return $this->toLink();
    }

    /**
     * @return string
     */
    public function toTagFriendRouter(): ?string
    {
        return $this->toRouter();
    }

    public function privacyItem(): ?Content
    {
        return $this->item;
    }

    /**
     * @inheritDoc
     */
    public function hasTagStream(): bool
    {
        return false;
    }

    public function userHidden(): HasOne
    {
        $context = match (Auth::guest()) {
            true  => UserFacade::getGuestUser(),
            false => user(),
        };

        return $this->hasOne(CommentHide::class, 'item_id')
            ->where([
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
                'is_hidden' => true,
            ]);
    }

    public function getIsHiddenAttribute(): bool
    {
        return (bool) LoadReduce::get(
            sprintf('comment::isHidden(user:%s,comment:%s)', Auth::user()?->id, $this->id),
            fn () => $this->userHidden?->is_hidden,
        );
    }

    public function toReportTitle(): string
    {
        $itemTitle = $this->toTitle();

        return __p('comment::phrase.comment_in_item_title', [
            'item_title'      => $itemTitle,
            'comment_content' => $this->text_parsed ?? '',
        ]);
    }

    public function getParentCommentAttribute()
    {
        // prevent select comment where id=0
        if (!$this->parent_id) {
            return null;
        }

        return $this->getRelationValue('parentComment');
    }

    public function getTotalTagFriendAttribute(): int
    {
        return count($this->tagged_user_ids);
    }

    public function toReportHeader(): string
    {
        $itemTitle = $this->toTitle();

        return __p('comment::phrase.report_comment_in_item_title_header', [
            'item_title'      => $itemTitle,
            'comment_content' => $this->text_parsed ?? '',
        ]);
    }
}
