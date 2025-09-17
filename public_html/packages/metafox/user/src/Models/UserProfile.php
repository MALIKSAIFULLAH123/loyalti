<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Core\Support\Facades\Currency;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Core\Support\Facades\Timezone;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAvatarMorph;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasAvatarMorphTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasCoverMorphTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Profile\Support\Facade\CustomField;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class UserProfile.
 *
 * @property int             $id,
 * @property string          $full_phone_number
 * @property int             $gender_id
 * @property ?UserGender     $gender
 * @property string|null     $birthday
 * @property int|null        $birthday_doy
 * @property int|null        $birthday_month
 * @property string|null     $birthday_search
 * @property ?string         $country_iso
 * @property string          $country_state_id
 * @property string          $country_city_code
 * @property string          $city_location
 * @property string          $postal_code
 * @property string          $language_id
 * @property int             $style_id
 * @property int             $timezone_id
 * @property string          $currency_id
 * @property int             $dst_check
 * @property string          $server_id
 * @property int             $hide_tip
 * @property string          $status
 * @property int             $footer_bar
 * @property int             $invite_user_id
 * @property int             $im_beep
 * @property int             $im_hide
 * @property int             $total_spam
 * @property User            $user
 * @property User            $invite_user
 * @property int             $previous_relation_type
 * @property int             $previous_relation_with
 * @property int             $relation
 * @property int             $relation_with
 * @property string          $cover_photo_position
 * @property string          $possessive_gender
 * @property string|null     $relationship_text
 * @property string          $address
 * @property UserRelation    $relationship
 * @property Collection|null $preferences_value
 * @property string|null     $user_preference_profile_theme_type
 * @mixin  Builder
 */
class UserProfile extends Model implements Entity, HasAvatarMorph, HasCoverMorph
{
    use HasEntity;
    use HasAvatarMorphTrait;
    use HasCoverMorphTrait;

    public const ENTITY_TYPE = 'user_profile';

    public const BIO_MAX_LENGTH_LIMIT = 100;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'full_phone_number',
        'gender_id',
        'birthday',
        'birthday_doy',
        'birthday_month',
        'birthday_search',
        'country_iso',
        'country_state_id',
        'country_city_code',
        'city_location',
        'postal_code',
        'language_id',
        'style_id',
        'timezone_id',
        'currency_id',
        'dst_check',
        'hide_tip',
        'status',
        'footer_bar',
        'invite_user_id',
        'im_beep',
        'im_hide',
        'total_spam',
        'previous_relation_type',
        'previous_relation_with',
        'relation',
        'relation_with',
        'avatar_type',
        'avatar_id',
        'avatar_file_id',
        'cover_id',
        'cover_type',
        'cover_file_id',
        'cover_photo_position',
        'address',
    ];

    /**
     * @var string[]
     */
    protected $appends = ['possessive_gender'];

    protected $with = ['preferences'];

    // where to store resources ?
    public array $fileColumns = [
        'avatar_file_id' => 'photo',
        'cover_file_id'  => 'photo',
    ];

    public function isOwner(User $user): bool
    {
        return $this->id == $user->entityId();
    }

    /**
     * @return array[]
     */
    public function getProfileMenus(): array
    {
        // @TODO get real menus
        return [
            'friend' => [
                'label'  => 'friend(s)',
                'path'   => 'userFriends',
                'params' => [
                    'headerTitle' => 'Friends',
                    'query'       => [
                        'user_id'    => $this->id,
                        'profile_id' => $this->id,
                        'limit'      => 12,
                    ],
                ],
            ],
            'blog' => [
                'label'  => 'Blogs',
                'path'   => 'blog/list-item',
                'params' => [
                    'headerTitle' => 'Blogs',
                    'query'       => [
                        'user_id'    => $this->id,
                        'profile_id' => $this->id,
                        'limit'      => 12,
                    ],
                ],
            ],
            'pages' => [
                'label'  => 'Pages',
                'path'   => 'pages/list-item',
                'params' => [
                    'headerTitle' => 'Pages',
                    'query'       => [
                        'user_id'    => $this->id,
                        'profile_id' => $this->id,
                        'limit'      => 12,
                    ],
                ],
            ],
        ];
    }

    public function getPossessiveGenderAttribute(): string
    {
        return UserFacade::getPossessiveGender($this->gender);
    }

    public function getBioAttribute(?string $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return MetaFoxConstant::EMPTY_STRING;
    }

    public function getAboutMeAttribute(?string $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return MetaFoxConstant::EMPTY_STRING;
    }

    public function getInterestAttribute(?string $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return MetaFoxConstant::EMPTY_STRING;
    }

    public function getHobbiesAttribute(?string $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return MetaFoxConstant::EMPTY_STRING;
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(UserGender::class, 'gender_id', 'id');
    }

    public function relationWithUser(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'relation_with', 'id')->withTrashed();
    }

    public function relationship(): HasOne
    {
        return $this->hasOne(UserRelation::class, 'id', 'relation');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'id', 'id')->withTrashed();
    }

    public function owner(): BelongsTo
    {
        return $this->user()->withTrashed();
    }

    public function userEntity(): BelongsTo
    {
        return $this->belongsTo(UserEntity::class, 'id', 'id')->withTrashed();
    }

    public function ownerEntity(): BelongsTo
    {
        return $this->userEntity()->withTrashed();
    }

    protected function timezoneId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getTimezoneId(),
            set: fn ($value) => ['timezone_id' => $value],
        );
    }

    protected function getTimezoneId(): int
    {
        $timezoneId = Arr::get($this->attributes, 'timezone_id', 0);

        if ($timezoneId) {
            return $timezoneId;
        }

        return Timezone::getDefaultTimezoneId();
    }

    protected function getLanguageIdAttribute(?string $value): string
    {
        $locales = Language::availableLocales();

        if (!is_string($value) || !in_array($value, $locales)) {
            return Language::getDefaultLocaleId();
        }

        return $value;
    }

    protected function getCurrencyIdAttribute(): string
    {
        return Arr::get($this->attributes, 'currency_id') ?? Currency::getDefaultCurrencyId();
    }

    protected function getRelationshipTextAttribute(): ?string
    {
        if (!CustomField::isEnabledRelationshipStatus()) {
            return null;
        }

        $phraseVar = $this?->relationship?->phrase_var;
        if ($phraseVar == null) {
            return null;
        }

        return __p($phraseVar);
    }

    protected function countryIso(): Attribute
    {
        return Attribute::make(
            function (?string $value) {
                if (!Country::getCountry($value)) {
                    return null;
                }

                return $value;
            }
        );
    }

    public function getUserAttribute()
    {
        return LoadReduce::getEntity('user', $this->id, fn () => $this->getRelationValue('user'));
    }

    public function getOwnerAttribute()
    {
        return LoadReduce::getEntity('user', $this->id, fn () => $this->getRelationValue('user'));
    }

    public static function booted()
    {
        static::retrieved(function ($model) {
            LoadReduce::put(sprintf('entity::user_profile-%s', $model->id), $model);
        });
    }

    public function genderObject()
    {
        if (!$this->gender_id) {
            return null;
        }

        return Cache::rememberForever(
            "genderObject::{$this->gender_id}",
            function () {
                $gender = UserGender::find($this->gender_id, ['id', 'phrase', 'is_custom'])?->toArray();

                if (!is_array($gender)) {
                    return null;
                }

                return array_merge($gender, [
                    'possessive_gender' => $this->possessive_gender,
                    'title'             => is_string($gender['phrase']) ? __p($gender['phrase']) : null,
                ]);
            },
        );
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class, 'user_id', 'id')->where('user_type', 'user');
    }

    public function getPreferencesValueAttribute(): ?Collection
    {
        $data = $this->preferences;

        return collect($data)->pluck('value', 'name');
    }

    public function getUserPreferenceProfileThemeTypeAttribute(): ?string
    {
        $enforce = Settings::get('core.general.enforce_display_mode');

        if (is_string($enforce) && '' !== $enforce) {
            return $enforce;
        }

        return null;
    }
}
