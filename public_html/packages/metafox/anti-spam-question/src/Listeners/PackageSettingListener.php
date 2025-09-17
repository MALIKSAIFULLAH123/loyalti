<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\AntiSpamQuestion\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub
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
            'require_all_spam_questions_on_signup' => [
                'value' => false,
            ],
            'enable_spam_question_on_signup'       => [
                'value' => false,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'user.registration.extra_field.rules'  => [
                AntiSpamQuestionRegistrationFieldRulesListener::class,
            ],
            'user.registration.extra_fields.build' => [
                AntiSpamQuestionRegistrationFieldsListener::class,
            ],
        ];
    }
}
