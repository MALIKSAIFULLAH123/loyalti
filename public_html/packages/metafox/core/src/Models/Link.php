<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Core\Database\Factories\LinkFactory;
use MetaFox\Core\Notifications\NewPostLinkToFollowerNotification;
use MetaFox\Platform\Contracts\ActivityFeedForm;
use MetaFox\Platform\Contracts\ActivityFeedSourceCanEditAttachment;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasBackGroundStatus;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasBackGroundStatusTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasTaggedFriendTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Link.
 *
 * @property        int    $id
 * @property        string $title
 * @property        string $link
 * @property        string $host
 * @property        string $image
 * @property        string $description
 * @property        string $feed_content
 * @property        bool   $has_embed
 * @method   static LinkFactory factory(...$parameters)
 */
class Link extends Model implements
    Content,
    ActivityFeedSourceCanEditAttachment,
    ActivityFeedForm,
    HasPrivacy,
    AppendPrivacyList,
    HasTotalLike,
    HasTotalCommentWithReply,
    HasTotalShare,
    HasTaggedFriend,
    HasLocationCheckin,
    HasSavedItem,
    HasApprove,
    HasBackGroundStatus
{
    use HasContent;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasFactory;
    use AppendPrivacyListTrait;
    use HasTaggedFriendTrait;
    use HasBackGroundStatusTrait;

    public const ENTITY_TYPE = 'link';

    public const FEED_POST_TYPE = self::ENTITY_TYPE;

    protected $table = 'core_links';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy',
        'total_like',
        'total_share',
        'title',
        'link',
        'host',
        'image',
        'description',
        'feed_content',
        'has_embed',
        'location_latitude',
        'location_longitude',
        'location_name',
        'is_approved',
        'is_preview_hidden',
        'status_background_id',
        'total_tag_friend',
        'total_pending_reply',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'location_address',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'has_embed'         => 'boolean',
        'is_preview_hidden' => 'boolean',
    ];

    /**
     * @return LinkFactory
     */
    protected static function newFactory(): LinkFactory
    {
        return LinkFactory::new();
    }

    public function getFeedContent(): ?string
    {
        return $this->feed_content;
    }

    public function toActivityFeed(): ?FeedAction
    {
        return new FeedAction([
            'user_id'    => $this->userId(),
            'user_type'  => $this->userType(),
            'owner_id'   => $this->ownerId(),
            'owner_type' => $this->ownerType(),
            'item_id'    => $this->entityId(),
            'item_type'  => $this->entityType(),
            'type_id'    => $this->entityType(),
            'privacy'    => $this->privacy,
            'content'    => $this->getFeedContent(),
        ]);
    }

    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, null, $this->location_address];
    }

    public function toSavedItem(): array
    {
        return [
            'title'          => $this->title,
            'image'          => $this->image,
            'item_type_name' => __p("core::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => $this->image ? 1 : 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toTitle(): string
    {
        return $this->title;
    }

    public function toLink(): ?string
    {
        $feed = $this->activity_feed;

        if ($feed?->entityType() === 'feed') {
            return url_utility()->makeApiResourceUrl($feed->entityType(), $feed->entityId());
        }

        return url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
    }

    public function toRouter(): ?string
    {
        $feed = $this->activity_feed;

        if ($feed?->entityType() === 'feed') {
            return url_utility()->makeApiResourceUrl($feed->entityType(), $feed->entityId());
        }

        return url_utility()->makeApiMobileResourceUrl($this->entityType(), $this->entityId());
    }

    public function toUrl(): ?string
    {
        $feed = $this->activity_feed;

        if ($feed?->entityType() === 'feed') {
            return url_utility()->makeApiResourceFullUrl($feed->entityType(), $feed->entityId());
        }

        return url_utility()->makeApiResourceFullUrl($this->entityType(), $this->entityId());
    }

    /**
     * @deprecated Remove in 5.1.13
     * @return array<string, mixed>|null
     */
    public function getBackgroundStatusImage(): ?array
    {
        $backgroundId = Arr::get($this->attributes, 'status_background_id');

        if (null === $backgroundId) {
            return null;
        }

        $images = app('events')->dispatch('background-status.get_bg_status_image', [$backgroundId], true);

        if (is_array($images)) {
            return $images;
        }

        return null;
    }

    public function getKeepCommentItemRouterAttribute(): bool
    {
        return true;
    }

    /**
     * @return array<int, mixed>
     */
    public function toFeedOGImages(): array
    {
        $image = $this->image;

        return ['origin' => $image];
    }

    public function toFollowerNotification(): ?array
    {
        $content = $this->feed_content;
        if (is_string($content)) {
            $attributeParser = [
                'parse_url' => false,
            ];
            app('events')->dispatch('core.parse_content', [$this, &$content, $attributeParser]);
        }

        $message = __p('core::phrase.user_name_create_a_new_post', [
            'title'     => $content ?? '',
            'isTitle'   => (int) !empty($this->toTitle()),
            'user_name' => $this->user->full_name,
        ]);

        $notification = new NewPostLinkToFollowerNotification();

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => [$this->user],
            'type'    => $notification->getType(),
        ];
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        return $this->description;
    }
}
