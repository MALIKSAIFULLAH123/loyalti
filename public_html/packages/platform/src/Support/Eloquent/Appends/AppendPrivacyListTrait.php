<?php

namespace MetaFox\Platform\Support\Eloquent\Appends;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * Trait AppendPrivacyListTrait.
 * @mixin Model
 * @mixin Builder
 * @property int $privacy
 */
trait AppendPrivacyListTrait
{
    /**
     * @var int[]
     */
    public array $privacy_list;

    public function loadPrivacyListAttribute()
    {
        $lists = PrivacyPolicy::getPrivacyItem($this);

        $listIds = [];
        if (!empty($lists)) {
            $listIds = array_column($lists, 'item_id');
        }

        $this->privacy_list = $listIds;
    }

    /**
     * @param array $privacyList
     */
    public function setPrivacyListAttribute($privacyList = [])
    {
        if ($this->privacy == MetaFoxPrivacy::CUSTOM) {
            $this->privacy_list = $privacyList;
            $this->updated_at   = Carbon::now(); //trigger event updated
        }
    }

    /**
     * @return array|null
     */
    public function getPrivacyListAttribute(): ?array
    {
        return $this->privacy == MetaFoxPrivacy::CUSTOM ? $this->privacy_list : null;
    }
}
