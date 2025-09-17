<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Music\Database\Factories\PlaylistFactory;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasGlobalSearch;
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
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Playlist.
 * @property        mixed           $view_id
 * @property        mixed           $privacy
 * @property        mixed           $is_featured
 * @property        mixed           $is_sponsor
 * @property        mixed           $sponsor_in_feed
 * @property        mixed           $description
 * @property        mixed           $image_file_id
 * @property        mixed           $year
 * @property        mixed           $image_path
 * @property        mixed           $server_id
 * @property        mixed           $module_id
 * @property        mixed           $total_track
 * @property        mixed           $total_play
 * @property        mixed           $total_like
 * @property        mixed           $total_comment
 * @property        mixed           $total_attachment
 * @property        mixed           $total_rating
 * @property        mixed           $total_length
 * @property        mixed           $total_score
 * @property        mixed           $album_type
 * @property        mixed           $user_id
 * @property        mixed           $user_type
 * @property        mixed           $owner_id
 * @property        mixed           $owner_type
 * @property        mixed           $total_view
 * @property        mixed           $songs
 * @property        string          $name
 * @property        bool            $is_favorite
 * @method   static PlaylistFactory factory($count = null, $state = [])
 */
class Playlist extends Model implements
    Content,
    AppendPrivacyList,
    ActivityFeedSource,
    HasPrivacy,
    HasThumbnail,
    HasResourceStream,
    HasTotalAttachment,
    HasTotalLike,
    HasTotalView,
    HasGlobalSearch,
    HasSavedItem,
    HasTotalComment,
    HasTotalShare,
    HasTotalCommentWithReply
{
    use HasContent;
    use HasUserMorph;
    use HasOwnerMorph;
    use AppendPrivacyListTrait;
    use HasTotalAttachmentTrait;
    use HasNestedAttributes;
    use HasThumbnailTrait;
    use HasFactory;

    public const ENTITY_TYPE = 'music_playlist';

    protected $table = 'music_playlists';

    /** @var array<mixed> */
    public $nestedAttributes = [
        'songs',
    ];

    public $casts = [
        'is_featured' => 'boolean',
        'is_sponsor'  => 'boolean',
        'is_active'   => 'boolean',
    ];

    protected $fillable = [
        'view_id',
        'privacy',
        'is_featured',
        'is_sponsor',
        'sponsor_in_feed',
        'name',
        'description',
        'image_file_id',
        'year',
        'image_path',
        'server_id',
        'module_id',
        'total_track',
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

    protected static function newFactory(): PlaylistFactory
    {
        return PlaylistFactory::new();
    }

    public function privacyStreams(): HasMany
    {
        return $this->hasMany(PlaylistPrivacyStream::class, 'item_id', 'id');
    }

    public function toActivityFeed(): ?FeedAction
    {
        return null;
    }

    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        return [
            'title' => $this->name,
            'text'  => $this->description ?? '',
        ];
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->total_length;
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(FavouriteData::class, 'item_id', 'id')
            ->where('item_type', 'music_playlist');
    }

    public function isFavorite(): HasOne
    {
        return $this->hasOne(FavouriteData::class, 'item_id', 'id')
            ->where('item_type', 'music_playlist')
            ->where('user_id', '=', Auth::user()?->id ?? 0);
    }

    public function getIsFavoriteAttribute(): bool
    {
        return $this->getRelationValue('isFavorite')?->user_id > 0;
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

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(
            Song::class,
            'music_playlist_data',
            'playlist_id',
            'item_id'
        )->using(PlaylistData::class);
    }

    public function getSizes(): array
    {
        return ['240', '500'];
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl(sprintf('/%s/%s/%s', 'music/playlist', $this->entityId(), $this->toSlug()));
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl(sprintf('/%s/%s/%s', 'music/playlist', $this->entityId(), $this->toSlug()));
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('music/playlist/' . $this->entityId());
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
            'title' => __p('music::phrase.sponsor_music_playlist_title', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function toOGDescription(?User $context = null): ?string
    {
        return $this->description;
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
