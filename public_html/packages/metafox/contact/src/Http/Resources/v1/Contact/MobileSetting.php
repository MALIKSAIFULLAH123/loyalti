<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Contact\Http\Resources\v1\Contact;

use MetaFox\Form\Constants;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('addItem')
            ->apiMethod(Constants::METHOD_GET)
            ->apiUrl('core/mobile/form/contact.store');
    }
}
