<?php

namespace MetaFox\AntiSpamQuestion\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;


/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'antispamquestion';
        $vars   = [
            'antispamquestion.require_all_spam_questions_on_signup',
            'antispamquestion.enable_spam_question_on_signup',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::switch('antispamquestion.enable_spam_question_on_signup')
                ->label(__p('antispamquestion::admin.enable_spam_question_on_signup_label')),
            Builder::switch('antispamquestion.require_all_spam_questions_on_signup')
                ->label(__p('antispamquestion::admin.require_all_spam_questions_on_signup_label'))
                ->description(__p('antispamquestion::admin.require_all_spam_questions_on_signup_desc')),
        );

        $this->addDefaultFooter(true);
    }
}
