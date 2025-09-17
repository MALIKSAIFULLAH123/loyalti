<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserInactive\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 *--------------------------------------------------------------------------
 * User Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class UserWebSetting.
 * @@SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('processMailingAll')
            ->asGet()
            ->apiUrl('admincp/core/form/user.inactive_process.create')
            ->apiParams([
                'q'                => ':q',
                'email'            => ':email',
                'group'            => ':group',
                'status'           => ':status',
                'gender'           => ':gender',
                'postal_code'      => ':postal_code',
                'country_state_id' => ':country_state_id',
                'country'          => ':country',
                'day'              => ':day',
                'age_from'         => ':age_from',
                'age_to'           => ':age_to',
                'sort'             => ':sort',
                'ip_address'       => ':ip_address',
                'phone_number'     => ':phone_number',
            ]);

        $this->add('searchForm')
            ->apiUrl('admincp/core/form/user.inactive.search_form');
    }
}
