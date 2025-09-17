<?php

namespace MetaFox\Mobile\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm;
use MetaFox\Form\Builder;
use MetaFox\Localize\Support\Traits\TranslatableSettingFieldTrait;
use MetaFox\Mobile\Facades\Mobile;
use MetaFox\Mobile\Http\Requests\v1\SiteSettingForm\Admin\UpdateSiteSettingRequest;
use MetaFox\Platform\Facades\Settings;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SmartBannerSettingForm.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SmartBannerSettingForm extends AdminSettingForm
{
    use TranslatableSettingFieldTrait;

    protected array $varsTranslatable = [
        'mobile.smart_banner.title',
        'mobile.smart_banner.button',
        'mobile.smart_banner.store_text.android',
        'mobile.smart_banner.store_text.ios',
        'mobile.smart_banner.price.android',
        'mobile.smart_banner.price.ios',
    ];

    protected function prepare(): void
    {
        $value = [];

        $vars = [
            'mobile.google_app_id',
            'mobile.apple_app_id',
            'mobile.smart_banner.position',
        ];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/mobile/setting/mobile.smart_banner')
            ->asPut()
            ->setValue($value);

        $this->prepareTranslatable();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::translatableText('mobile.smart_banner.title')
                    ->label(__p('mobile::phrase.smart_banner_tile_label'))
                    ->description(__p('mobile::phrase.smart_banner_tile_desc'))
                    ->buildFields(),
                Builder::translatableText('mobile.smart_banner.button')
                    ->label(__p('mobile::phrase.smart_banner_button_label'))
                    ->description(__p('mobile::phrase.smart_banner_button_desc'))
                    ->buildFields(),

                // Android Settings
                Builder::translatableText('mobile.smart_banner.store_text.android')
                    ->label(__p('mobile::phrase.smart_banner_store_text_android_label'))
                    ->description(__p('mobile::phrase.smart_banner_store_text_android_desc'))
                    ->buildFields(),
                Builder::translatableText('mobile.smart_banner.price.android')
                    ->label(__p('mobile::phrase.smart_banner_price_android_label'))
                    ->description(__p('mobile::phrase.smart_banner_price_android_desc'))
                    ->buildFields(),

                // iOS Settings
                Builder::translatableText('mobile.smart_banner.store_text.ios')
                    ->label(__p('mobile::phrase.smart_banner_store_text_iso_label'))
                    ->description(__p('mobile::phrase.smart_banner_store_text_ios_desc'))
                    ->buildFields(),
                Builder::translatableText('mobile.smart_banner.price.ios')
                    ->label(__p('mobile::phrase.smart_banner_price_ios_label'))
                    ->description(__p('mobile::phrase.smart_banner_price_ios_desc'))
                    ->buildFields(),
                Builder::choice('mobile.smart_banner.position')
                    ->required()
                    ->disableClearable()
                    ->label(__p('mobile::phrase.smart_banner_position_label'))
                    ->description(__p('mobile::phrase.smart_banner_position_desc'))
                    ->options(Mobile::getSmartBannerPositionOptions()),
                Builder::text('mobile.google_app_id')
                    ->label(__p('mobile::phrase.google_app_id_label'))
                    ->description(__p('mobile::phrase.google_app_id_desc')),
                Builder::text('mobile.apple_app_id')
                    ->label(__p('mobile::phrase.apple_app_id_label'))
                    ->description(__p('mobile::phrase.apple_app_id_desc')),
            );

        $this->addDefaultFooter(true);
    }

    /**
     * validated.
     *
     * @param UpdateSiteSettingRequest $request
     *
     * @return array<mixed>
     */
    public function validated(UpdateSiteSettingRequest $request): array
    {
        $data = $request->all();

        $this->saveTranslatableValue($data);

        return $data;
    }
}
