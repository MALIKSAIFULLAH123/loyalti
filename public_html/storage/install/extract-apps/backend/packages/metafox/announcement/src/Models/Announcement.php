<?php

namespace MetaFox\Announcement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use MetaFox\Announcement\Database\Factories\AnnouncementFactory;
use MetaFox\Authorization\Models\Role;
use MetaFox\Localize\Models\Country as MainModel;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserAsOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserGender;

/**
 * Class Announcement.
 *
 * @mixin Builder
 *
 * @property        int                      $id
 * @property        int                      $is_active
 * @property        int                      $can_be_closed
 * @property        int                      $show_in_dashboard
 * @property        string                   $start_date
 * @property        string                   $country_iso
 * @property        int                      $gender
 * @property        int                      $age_from
 * @property        int                      $age_to
 * @property        string                   $gmt_offset
 * @property        string                   $subject_var
 * @property        string                   $intro_var
 * @property        string                   $intro
 * @property        string                   $title
 * @property        string                   $created_at
 * @property        string                   $updated_at
 * @property        AnnouncementText         $announcementText  ***Deprecated: Do not use this attribute. Use contents
 *                  and content instead***
 * @property        Collection               $contents
 * @property        AnnouncementContent|null $masterContent
 * @property        AnnouncementContent|null $content
 * @property        string                   $admin_edit_url
 * @property        string                   $admin_browse_url
 * @property        Style                    $style
 * @property        Collection               $views
 * @property        int                      $total_view        Deprecated: Do not use this attribute.
 * @property        Collection               $roles
 * @property        Collection               $genders
 * @property        Collection               $countries
 * @method   static AnnouncementFactory      factory()
 */
class Announcement extends Model implements
    Content,
    HasTotalView,
    HasTotalLike,
    HasTotalComment,
    HasPrivacy,
    HasTotalCommentWithReply
{
    use HasContent;
    use HasUserMorph;
    use HasUserAsOwnerMorph;
    use HasNestedAttributes;
    use HasFactory;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'announcement';
    public const IS_ACTIVE   = 1;

    protected $table = 'announcements';

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'roles',
        'genders',
        'countries',
    ];

    /**
     * @var array<string>
     */
    public array $translatableAttributes = [
        'subject_var',
        'intro_var',
    ];

    /** @var string[] */
    protected $fillable = [
        'is_active',
        'can_be_closed',
        'show_in_dashboard',
        'start_date',
        'country_iso',
        'gender',
        'age_from',
        'age_to',
        'user_id',
        'user_type',
        'gmt_offset',
        'style_id',
        'subject_var',
        'intro_var',
        'total_view',
        'total_pending_comment',
        'total_pending_reply',
    ];

    protected $appends = [
        'title',
        'intro',
    ];

    /**
     * @return HasOne
     * @deprecated will be remove soon => DO NOT USE
     */
    public function announcementText(): HasOne
    {
        return $this->hasOne(AnnouncementText::class, 'id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id', 'id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(AnnouncementView::class, 'announcement_id', 'id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(AnnouncementContent::class, 'announcement_id', 'id');
    }

    public function masterContent(): HasOne
    {
        return $this->hasOne(AnnouncementContent::class, 'announcement_id', 'id')->where('locale', 'en');
    }

    public function content(): HasOne
    {
        $locale = app()->getLocale() ?: 'en';

        return $this->hasOne(AnnouncementContent::class, 'announcement_id', 'id')->where('locale', $locale);
    }

    /**
     * @return AnnouncementFactory
     */
    protected static function newFactory(): AnnouncementFactory
    {
        return AnnouncementFactory::new();
    }

    public function toTitle(): string
    {
        return $this->title;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'announcement_role_data',
            'announcement_id',
            'role_id'
        )->using(AnnouncementData::class);
    }

    public function genders(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGender::class,
            'announcement_gender_data',
            'item_id',
            'gender_id'
        )->using(GenderData::class);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(
            MainModel::class,
            'announcement_country_data',
            'item_id',
            'country_iso',
            'id',
            'country_iso',
        )->using(CountryData::class);
    }

    public function getTitleAttribute(): string
    {
        if (!is_string($this->subject_var)) {
            return '';
        }

        $title = __p($this->subject_var);

        return htmlspecialchars_decode($title);
    }

    public function getIntroAttribute(): string
    {
        if (!is_string($this->intro_var)) {
            return '';
        }

        $intro = __p($this->intro_var);

        return htmlspecialchars_decode($intro);
    }

    public function getAdminEditUrlAttribute()
    {
        return sprintf('/announcement/announcement/edit/%s', $this->entityId());
    }

    public function getAdminBrowseUrlAttribute()
    {
        return sprintf('/announcement/announcement/browse');
    }
}
