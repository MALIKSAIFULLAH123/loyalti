<?php

namespace MetaFox\Payment\Http\Resources\v1\Gateway\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 * --------------------------------------------------------------------------
 *  Country Web Resource Config
 * --------------------------------------------------------------------------
 *  stub: /packages/resources/resource_setting.stub.
 */

/**
 * Class WebSetting.
 * @author   developer@phpfox.com
 * @license  phpfox.com
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('editItem')
            ->apiUrl('admincp/payment-gateway/form/:id');
    }
}
