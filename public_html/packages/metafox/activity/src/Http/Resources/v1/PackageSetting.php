<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Http\Resources\v1;

use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Activity\Support\TypeManager;
use MetaFox\Platform\Facades\Settings;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        return [
            'types'        => resolve(TypeManager::class)->getTypeSettings(),
            'sort_default' => Settings::get('activity.feed.sort_default', SortScope::SORT_DEFAULT),
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'types'        => resolve(TypeManager::class)->getTypeSettings(),
            'sort_default' => Settings::get('activity.feed.sort_default', SortScope::SORT_DEFAULT),
        ];
    }
}
