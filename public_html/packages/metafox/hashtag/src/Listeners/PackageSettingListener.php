<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Hashtag\Listeners;

use MetaFox\Hashtag\Models\Tag as Hashtag;
use MetaFox\Hashtag\Policies\HashtagPolicy;
use MetaFox\Platform\Support\BasePackageSettingListener;

class PackageSettingListener extends BasePackageSettingListener
{
    public function getPolicies(): array
    {
        return [
            Hashtag::class => HashtagPolicy::class,
        ];
    }

    public function getUserPermissions(): array
    {
        return [

        ];
    }

    public function getEvents(): array
    {
        return [
            'hashtag.create_hashtag' => [
                ItemTagAwareListener::class,
            ],
            'hashtag.update_total_item' => [
                UpdateTotalItemListener::class,
            ],
            'tag.get_search_resource' => [
                GetSearchResourceListener::class,
            ],
            'tag.get_id' => [
                GetTagIdListener::class,
            ],
        ];
    }
}
