<?php

namespace MetaFox\Newsletter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Authorization\Models\Role;
use MetaFox\Localize\Models\Country;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserGender;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Newsletter.
 *
 * @property int            $id
 * @property        string  $subject
 * @property        string  $subject_raw
 * @property int            $user_id
 * @property int            $age_from
 * @property int            $age_to
 * @property int            $round
 * @property int            $status
 * @property int            $archive
 * @property int            $override_privacy
 * @property int            $total_sent
 * @property int            $total_users
 * @property int            $last_sent_id
 * @property array          $channels
 * @property Collection     $roles
 * @property Collection     $genders
 * @property Collection     $countries
 * @property NewsletterText $newsletterText
 */
class Newsletter extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;
    use HasNestedAttributes;
    use SoftDeletes;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'newsletter';

    public const NOT_STARTED_STATUS = 0;
    public const PENDING_STATUS     = 1;
    public const SENDING_STATUS     = 2;
    public const COMPLETED_STATUS   = 3;
    public const STOPPED_STATUS     = 4;

    protected $table = 'newsletters';

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
        'subject',
    ];

    /** @var string[] */
    protected $fillable = [
        'subject',
        'user_id',
        'user_type',
        'age_from',
        'age_to',
        'round',
        'status',
        'archive',
        'channels',
        'override_privacy',
        'total_sent',
        'total_users',
        'last_sent_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'channels' => 'array',
    ];

    /**
     * @return HasOne
     */
    public function newsletterText(): HasOne
    {
        return $this->hasOne(NewsletterText::class, 'id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'newsletter_role_data',
            'newsletter_id',
            'role_id',
        )->using(RoleData::class);
    }

    /**
     * @return BelongsToMany
     */
    public function genders(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGender::class,
            'newsletter_gender_data',
            'newsletter_id',
            'gender_id',
        )->using(GenderData::class);
    }

    /**
     * @return BelongsToMany
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(
            Country::class,
            'newsletter_country_data',
            'newsletter_id',
            'country_iso',
            'id',
            'country_iso',
        )->using(CountryData::class);
    }

    /**
     * @return array
     */
    public function rolesIds(): array
    {
        return $this->roles->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    public function genderIds(): array
    {
        return $this->genders->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    public function countryIds(): array
    {
        return $this->countries->pluck('country_iso')->toArray();
    }

    public function processText(): string
    {
        if ($this->total_sent == 0 && in_array($this->status, [self::NOT_STARTED_STATUS, self::PENDING_STATUS])) {
            return $this->statusText();
        }

        return __p(
            'newsletter::phrase.process_sent_emails',
            ['current' => $this->total_sent, 'total' => $this->total_users]
        );
    }

    public function statusText(): string
    {
        return match ($this->status) {
            self::NOT_STARTED_STATUS => __p('newsletter::phrase.not_started'),
            self::PENDING_STATUS     => __p('core::phrase.pending'),
            self::SENDING_STATUS     => __p('newsletter::phrase.sending'),
            self::COMPLETED_STATUS   => __p('newsletter::phrase.completed'),
            self::STOPPED_STATUS     => __p('newsletter::phrase.status_stopped'),
        };
    }

    public function getSubjectAttribute(): string
    {
        return __p($this->subject_raw);
    }

    public function getSubjectRawAttribute(): ?string
    {
        return Arr::get($this->attributes, 'subject', '');
    }
}

// end
