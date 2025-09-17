<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Interface TempFileModel.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $owner_id
 * @property string $owner_type
 * @property string $type_id
 * @property float  $px
 * @property float  $py
 * @mixin HasUserMorph
 * @mixin HasEntity
 * @mixin HasOwnerMorph
 * @mixin HasItemMorph
 * @mixin Builder
 */
interface TagFriendModel
{
}
