<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\RegexRule\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getSiteSettings(): array
    {
        return [
            'user_name_regex_rule'          => ['value' => '^[a-zA-Z0-9_\-]+$'],
            'user_name_regex_error_message' => [
                'type'      => 'string',
                'value'     => 'regex::phrase.user_name_regex_error_message',
                'is_public' => false,
            ],
            'display_name_regex_rule'          => ['value' => '^[^!@#$%^&*(),.?":{}|<>]+$'],
            'display_name_regex_error_message' => [
                'type'      => 'string',
                'value'     => 'regex::phrase.display_name_regex_error_message',
                'is_public' => false,
            ],
            'currency_id_regex_rule'          => ['value' => '^[A-Z]{1,3}$'],
            'currency_id_regex_error_message' => [
                'type'      => 'string',
                'value'     => 'regex::phrase.currency_id_regex_error_message',
                'is_public' => false,
            ],
        ];
    }
}
