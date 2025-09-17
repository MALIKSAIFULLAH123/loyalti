<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Profile\Database\Factories\ProfileFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * class Profile.
 *
 * @property int    $id
 * @property string $profile_type
 * @property string $title
 * @property string $description
 *
 * @method static ProfileFactory factory(...$parameters)
 */
class Profile extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_custom_profile';

    protected $table = 'user_custom_profiles';

    /** @var string[] */
    protected $fillable = [
        'user_type',
        'profile_type',
        'title',
        'description',
    ];

    public $timestamps = false;

    public function setTitleAttribute($value)
    {
        $key = 'profile::phrase.' . $this->profile_type;
        resolve(PhraseRepositoryInterface::class)->updatePhrases([$key => $value]);
    }

    public function setDescriptionAttribute($value)
    {
        $key = 'profile::phrase.' . $this->profile_type . '_description';
        resolve(PhraseRepositoryInterface::class)->updatePhrases([$key => $value]);
    }

    public function getTitleAttribute()
    {
        return __p('profile::phrase.' . $this->profile_type);
    }

    public function getDescriptionAttribute()
    {
        return __p('profile::phrase.' . $this->profile_type . '_description');
    }

    /**
     * @return ProfileFactory
     */
    protected static function newFactory()
    {
        return ProfileFactory::new();
    }
}

// end
