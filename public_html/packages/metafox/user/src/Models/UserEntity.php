<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\HasAvatar;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\UserEntity as ContractUserEntity;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasAvatarTrait;

/**
 * Class UserEntity.
 *
 * @mixin Builder
 * @property int                              $id
 * @property string                           $entity_type
 * @property string                           $user_name
 * @property string                           $name
 * @property string|null                      $display_name
 * @property string                           $short_name
 * @property bool                             $is_featured
 * @property string|null                      $sort_type                              - Note: do not use this outside
 *                                                                                    User Shortcut.
 * @property string|null                      $avatar_id                              - Note: do not use this outside
 *                                                                                    User Shortcut.
 * @property array                            $avatars
 * @property int                              $gender
 * @property string                           $possessive_gender
 * @method   UserEntity                       firstOrFail()
 * @method   UserEntity                       firstOrCreate($conditions, $conditions)
 * @property \MetaFox\Platform\Contracts\User $detail
 * @property string|null                      $deleted_at
 */
class UserEntity extends Model implements ContractUserEntity, HasAvatar, HasUrl
{
    use HasAvatarTrait;
    use SoftDeletes;

    public $incrementing = false;

    /** @var string[] */
    protected $fillable = [
        'id', 'entity_type', 'user_name', 'name', 'avatar_id', 'avatar_type', 'avatar_file_id', 'is_featured', 'gender',
        'is_searchable', 'short_name', 'deleted_at', 'is_suggestion_searchable',
    ];

    public $timestamps = false;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'is_suggestion_searchable' => 'boolean',
    ];

    /** @var string[] */
    protected $appends = ['possessive_gender'];

    public function entityId(): int
    {
        return $this->id;
    }

    public function entityType(): ?string
    {
        return $this->entity_type;
    }

    public function detail(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'id')->withTrashed();
    }

    public function getPossessiveGenderAttribute(): string
    {
        switch ($this->gender) {
            case MetaFoxConstant::GENDER_FEMALE:
                return __p('core::phrase.her');
            case MetaFoxConstant::GENDER_MALE:
                return __p('core::phrase.his');
        }

        return __p('core::phrase.their');
    }

    public function toLink(): ?string
    {
        return $this->detail->toLink();
    }

    public function toUrl(): ?string
    {
        if ($this->entityType() === User::ENTITY_TYPE) {
            return url_utility()->makeApiFullUrl($this->user_name);
        }

        return $this->detail->toUrl();
    }

    public function toRouter(): ?string
    {
        if ($this->entityType() === User::ENTITY_TYPE) {
            return url_utility()->makeApiMobileUrl($this->user_name);
        }

        return $this->detail->toRouter();
    }

    public function isDeleted(): bool
    {
        return (bool) $this->deleted_at;
    }


    public function getDetailAttribute()
    {
        return LoadReduce::getEntity($this->entity_type, $this->id, function () {
            return $this->getRelationValue('detail');
        });
    }

    public function getNameAttribute(?string $value): ?string
    {
        return ban_word()->clean($value);
    }

    public function getDisplayNameAttribute(): ?string
    {
        return Arr::get($this->getAttributes(), 'name');
    }

    public static function booted()
    {
        static::retrieved(function ($model) {
            LoadReduce::put(sprintf('entity::user_entity-%s', $model->id), $model);
        });
    }
}
