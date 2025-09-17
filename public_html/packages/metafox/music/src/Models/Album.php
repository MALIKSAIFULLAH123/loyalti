<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Music\Database\Factories\AlbumFactory;
use MetaFox\Music\Notifications\NewAlbumToFollowerNotification;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasPendingMode;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Album.
 *
 * @property        AlbumText|null $albumText
 * @property        Collection     $genres
 * @property        mixed          $view_id
 * @property        mixed          $is_featured
 * @property        mixed          $is_sponsor
 * @property        mixed          $sponsor_in_feed
 * @property        mixed          $image_file_id
 * @property        mixed          $year
 * @property        mixed          $image_path
 * @property        mixed          $server_id
 * @property        mixed          $module_id
 * @property        int            $total_track
 * @property        int            $total_play
 * @property        int            $total_like
 * @property        int            $total_comment
 * @property        int            $total_attachment
 * @property        int            $total_rating
 * @property        int            $total_length
 * @property        mixed          $total_score
 * @property        string         $album_type
 * @property        mixed          $total_view
 * @property        mixed          $total_share
 * @property        string         $name
 * @method   static AlbumFactory   factory($count = null, $state = [])
 */
class Album extends Model implements
    Content,
    ActivityFeedSource,
    AppendPrivacyList,
    HasPrivacy,
    HasFeature,
    HasResourceStream,
    HasTotalLike,
    HasThumbnail,
    HasTotalAttachment,
    HasGlobalSearch,
    HasSavedItem,
    HasTotalView,
    HasHashTag,
    HasTotalComment,
    HasTotalShare,
    HasTotalCommentWithReply
{
    use HasContent;
    use HasUserMorph;
    use HasOwnerMorph;
    use AppendPrivacyListTrait;
    use HasNestedAttributes;
    use HasTotalAttachmentTrait;
    use HasFactory;
    use HasThumbnailTrait;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'music_album';

    protected $table = 'music_albums';

    /** @var array<string, mixed> */
    public $nestedAttributes = [
        'albumText' => ['text', 'text_parsed'],
    ];

    protected $fillable = [
        'view_id',
        'privacy',
        'is_featured',
        'is_sponsor',
        'sponsor_in_feed',
        'image_file_id',
        'name',
        'year',
        'image_path',
        'server_id',
        'module_id',
        'total_track',
        'total_duration',
        'total_play',
        'total_like',
        'total_comment',
        'total_pending_comment',
        'total_pending_reply',
        'total_reply',
        'total_attachment',
        'total_rating',
        'total_length',
        'total_score',
        'total_view',
        'total_share',
        'album_type',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'created_at',
        'updated_at',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    public function toActivityFeed(): ?FeedAction
    {
        if (!$this->isApproved() && !$this->owner instanceof HasPendingMode) {
            return null;
        }

        return new FeedAction([
            'user_id'    => $this->userId(),
            'user_type'  => $this->userType(),
            'owner_id'   => $this->ownerId(),
            'owner_type' => $this->ownerType(),
            'item_id'    => $this->entityId(),
            'item_type'  => $this->entityType(),
            'type_id'    => $this->entityType(),
            'privacy'    => $this->privacy,
        ]);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            Genre::class,
            'music_genre_data',
            'item_id',
            'genre_id'
        )->using(GenreData::class)->wherePivot('music_genre_data.item_type', 'music_album');
    }

    /**
     * @return BelongsToMany
     */
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'music_album_tag_data',
            'item_id',
            'tag_id'
        )->using(AlbumTagData::class);
    }

    public function albumText(): HasOne
    {
        return $this->hasOne(AlbumText::class, 'id', 'id');
    }

    public function getThumbnail(): ?string
    {
        return (string) ($this->thumbnail_file_id ?? $this->image_file_id);
    }

    public function privacyStreams(): HasMany
    {
        return $this->hasMany(AlbumPrivacyStream::class, 'item_id', 'id');
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'album_id', 'id');
    }

    protected static function newFactory(): AlbumFactory
    {
        return AlbumFactory::new();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(FavouriteData::class, 'item_id', 'id')
            ->where('item_type', 'music_album');
    }

    public function isFavorite(): HasOne
    {
        return $this->hasOne(FavouriteData::class, 'item_id', 'id')
            ->where('item_type', 'music_album')
            ->where('user_id', '=', Auth::user()?->id ?? 0);
    }

    public function getIsFavoriteAttribute(): bool
    {
        return $this->getRelationValue('isFavorite')?->user_id > 0;
    }

    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        $modelText = $this->albumText;

        return [
            'title' => $this->name,
            'text'  => $modelText ? $modelText->text_parsed : '',
        ];
    }

    public function toSavedItem(): array
    {
        return [
            'title'          => ban_word()->clean($this->name),
            'image'          => $this->images,
            'item_type_name' => __p("music::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toTitle(): string
    {
        $title = Arr::get($this->attributes, 'name', MetaFoxConstant::EMPTY_STRING);

        return ban_word()->clean($title);
    }

    public function getSizes(): array
    {
        return ['240', '500'];
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl(sprintf('/%s/%s/%s', 'music/album', $this->entityId(), $this->toSlug()));
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl(sprintf('/%s/%s/%s', 'music/album', $this->entityId(), $this->toSlug()));
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('music/album/' . $this->entityId());
    }

    public function toFollowerNotification(): ?array
    {
        $message = __p('music::notification.user_name_created_a_new_album_music_title', [
            'title'     => $this->toTitle(),
            'user_name' => $this->user->full_name,
        ]);

        $notification = new NewAlbumToFollowerNotification();

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => [$this->user],
            'type'    => $notification->getType(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function toFeedOGImages(): array
    {
        $thumbnails = $this->images;

        return is_array($thumbnails) ? $thumbnails : [];
    }

    public function toSponsorData(): ?array
    {
        return [
            'title' => __p('music::phrase.sponsor_music_album_title', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function toOGDescription(?User $context = null): ?string
    {
        $this->loadMissing('albumText');
        $albumText = $this->albumText;

        if (!$albumText instanceof AlbumText) {
            return null;
        }

        return strip_tags($albumText->text_parsed);
    }

    public function toFeaturedData(): ?array
    {
        return [];
    }

    protected function toSlug(): string
    {
        $title = Arr::get($this->attributes, 'name');

        if (null === $title) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::slug(ban_word()->clean($title), language: null);
    }
}
