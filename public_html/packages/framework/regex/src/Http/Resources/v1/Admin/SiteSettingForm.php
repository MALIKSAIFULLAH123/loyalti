<?php

namespace MetaFox\RegexRule\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Localize\Support\Traits\TranslatableSettingFieldTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\RegexPatternRule;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    use TranslatableSettingFieldTrait;

    protected array $varsTranslatable = [
        'regex.user_name_regex_error_message',
        'regex.display_name_regex_error_message',
        'regex.currency_id_regex_error_message',
    ];

    protected function prepare(): void
    {
        $module = 'regex';
        $vars   = [
            'regex.user_name_regex_rule',
            'regex.display_name_regex_rule',
            'regex.currency_id_regex_rule',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);

        $this->prepareTranslatable();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('regex.user_name_regex_rule')
                    ->required()
                    ->label(__p('regex::phrase.username_regex_rule_label'))
                    ->description(__p('regex::phrase.username_regex_rule_description')),
                Builder::translatableText('regex.user_name_regex_error_message')
                    ->required()
                    ->label(__p('regex::phrase.user_name_regex_error_message_label'))
                    ->description(__p('regex::phrase.user_name_regex_error_message_desc'))
                    ->buildFields(),
                Builder::divider(),
                Builder::text('regex.display_name_regex_rule')
                    ->required()
                    ->label(__p('regex::phrase.display_name_regex_rule_label'))
                    ->description(__p('regex::phrase.display_name_regex_rule_description')),
                Builder::translatableText('regex.display_name_regex_error_message')
                    ->required()
                    ->label(__p('regex::phrase.display_name_regex_error_message_label'))
                    ->description(__p('regex::phrase.display_name_regex_error_message_desc'))
                    ->buildFields(),
                Builder::divider(),
                Builder::text('regex.currency_id_regex_rule')
                    ->required()
                    ->label(__p('regex::phrase.currency_id_regex_rule_label'))
                    ->description(__p('regex::phrase.currency_id_regex_rule_description')),
                Builder::translatableText('regex.currency_id_regex_error_message')
                    ->required()
                    ->label(__p('regex::phrase.currency_id_regex_error_message_label'))
                    ->description(__p('regex::phrase.currency_id_regex_error_message_desc'))
                    ->sx([])
                    ->buildFields(),
            );

        $this->addDefaultFooter(true);
    }

    /**
     * @param Request $request
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validated(Request $request): array
    {
        $data = $request->all();
        $this->saveTranslatableValue($data);

        $rules = [
            'regex.user_name_regex_rule'    => ['required', 'string', function ($attribute, $value, $fail) {
                if (preg_match('/\\\x7[f|F]|\\\x[8-9a-fA-F][0-9a-fA-F]/', $value)) {
                    $fail(__p('regex::phrase.name_must_not_allow_unicode', ['name' => __p('regex::phrase.username_regex_rule')]));
                }
            }, new RegexPatternRule(),
            ],
            'regex.display_name_regex_rule' => ['required', 'string', new RegexPatternRule()],
            'regex.currency_id_regex_rule'  => ['required', 'string', new RegexPatternRule()],
        ];

        $validator = Validator::make($data, $rules);
        $validator->validate();

        return $data;
    }
}
