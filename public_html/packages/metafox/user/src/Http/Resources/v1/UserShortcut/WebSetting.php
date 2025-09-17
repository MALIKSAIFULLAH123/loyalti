<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserShortcut;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\User\Models\UserShortcut;

/**
 *--------------------------------------------------------------------------
 * UserShortcut Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class UserShortcutWebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('user/shortcut');

        $this->add('viewEditShortcut')
            ->apiUrl('user/shortcut/edit')
            ->apiRules(['truthy', 'q']);

        $this->add('updateItem')
            ->apiUrl('user/shortcut/manage/:id')
            ->asPut()
            ->apiRules(['includes', 'sort_type', [UserShortcut::SORT_DEFAULT, UserShortcut::SORT_PIN, UserShortcut::SORT_HIDE]]);
    }
}
