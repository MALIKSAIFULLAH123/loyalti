<?php

namespace MetaFox\Localize\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Core\Support\Facades\Country as CountryFacade;
use MetaFox\Core\Support\Facades\Language as LanguageFacade;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;

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
    protected function prepare(): void
    {
        $module = 'localize';

        $vars = [
            'localize.disable_translation',
            'localize.display_translation_key',
            'localize.default_locale',
            'localize.default_country',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('localize::phrase.localize_settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::switch('localize.disable_translation')
                    ->label(__p('localize::phrase.translation_check_label'))
                    ->description(__p('localize::phrase.translation_check_desc')),
                Builder::switch('localize.display_translation_key')
                    ->label(__p('localize::phrase.display_translation_key_label'))
                    ->description(__p('localize::phrase.display_translation_key_desc')),
                Builder::choice('localize.default_locale')
                    ->label(__p('localize::phrase.default_locale_label'))
                    ->description(__p('localize::phrase.default_locale_desc'))
                    ->disableClearable()
                    ->options(LanguageFacade::getActiveOptions()),
                Builder::choice('localize.default_country')
                    ->label(__p('localize::phrase.default_country_label'))
                    ->disableClearable()
                    ->description(__p('localize::phrase.default_country_desc'))
                    ->options(CountryFacade::buildCountrySearchForm()),
            );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request): array
    {
        $rules = [
            'localize.disable_translation'     => ['boolean', new AllowInRule([true, false])],
            'localize.display_translation_key' => ['boolean', new AllowInRule([true, false])],
            'localize.default_locale'          => ['string', 'exists:core_languages,language_code,is_active,1'],
            'localize.default_country'         => ['string', 'exists:core_countries,country_iso,is_active,1'],
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules, [
            'localize.default_locale.string' => __p('localize::admin.please_choose_default_locale'),
            'localize.default_locale.exists' => __p('localize::admin.please_choose_default_locale'),
        ]);

        return $validator->validated();
    }
}
