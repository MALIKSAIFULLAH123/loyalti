<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Http\Resources\v1\Privacy;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Privacy Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('getCustomPrivacyOptions')
            ->apiUrl('core/custom-privacy-option')
            ->apiParams([
                'item_id'   => ':item_id',
                'item_type' => ':item_type',
            ])
            ->asGet();

        $this->add('getCreatePrivacyOptionForm')
            ->apiUrl('core/custom-privacy-option/form')
            ->asGet();
    }
}
