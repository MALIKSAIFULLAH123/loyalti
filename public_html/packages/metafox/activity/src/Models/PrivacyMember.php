<?php

namespace MetaFox\Activity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PrivacyMember.
 *
 * @mixin Builder
 * @property int $privacy_id
 * @property int $user_id
 * @property int $id
 */
class PrivacyMember extends Model
{
    public const ENTITY_TYPE = 'activity_privacy_member';

    protected $table = 'activity_privacy_members';

    protected $fillable = ['privacy_id', 'user_id'];

    public $timestamps = false;
}
