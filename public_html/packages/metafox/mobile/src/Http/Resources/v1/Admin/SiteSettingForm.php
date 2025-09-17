<?php

namespace MetaFox\Mobile\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AdminSettingForm
{
    protected function prepare(): void
    {
        $module = 'mobile';
        $vars   = [
            'mobile.admob_banner_uid.android',
            'mobile.admob_banner_uid.ios',
            'mobile.admob_interstitial_uid.android',
            'mobile.admob_interstitial_uid.ios',
            'mobile.admob_rewarded_uid.android',
            'mobile.admob_rewarded_uid.ios',
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
        // Adroid Settings
            Builder::text('mobile.admob_banner_uid.android')
                ->label(__p('mobile::phrase.admob_banner_uid_android_label'))
                ->description(__p('mobile::phrase.admob_banner_uid_android_desc', [
                    'url' => 'https://admob.google.com',
                ])),
            Builder::text('mobile.admob_interstitial_uid.android')
                ->label(__p('mobile::phrase.admob_interstitial_uid_android_label'))
                ->description(__p('mobile::phrase.admob_uid_android_desc')),
            Builder::text('mobile.admob_rewarded_uid.android')
                ->label(__p('mobile::phrase.admob_rewarded_uid_android_label'))
                ->description(__p('mobile::phrase.admob_uid_android_desc')),

            // iOS Settings
            Builder::text('mobile.admob_banner_uid.ios')
                ->label(__p('mobile::phrase.admob_banner_uid_ios_label'))
                ->description(__p('mobile::phrase.admob_uid_ios_desc')),
            Builder::text('mobile.admob_interstitial_uid.ios')
                ->label(__p('mobile::phrase.admob_interstitial_uid_label'))
                ->description(__p('mobile::phrase.admob_uid_ios_desc')),
            Builder::text('mobile.admob_rewarded_uid.ios')
                ->label(__p('mobile::phrase.admob_rewarded_uid_label'))
                ->description(__p('mobile::phrase.admob_uid_ios_desc')),
        );

        $this->addDefaultFooter(true);
    }

    public function alertMessage($modified = []): ?string
    {
        if (!$settings = Arr::get($modified, 'all')) {
            return null;
        }

        if ($this->shouldAlertToRebuildMobile()) {
            return $this->rebuildMobileMessage();
        }

        return $this->rebuildMessage();
    }
}
