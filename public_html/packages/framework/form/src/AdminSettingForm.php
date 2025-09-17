<?php

namespace MetaFox\Form;

use MetaFox\Platform\Facades\Settings;

abstract class AdminSettingForm extends AbstractForm
{
    /**
     * Default alert message to inform admin to rebuild the site.
     * @return string
     */
    protected function rebuildMessage(): string
    {
        return __p('core::phrase.please_rebuild_your_site');
    }

    /**
     * Default alert message to inform admin to rebuild the mobile app.
     * @return string
     */
    protected function rebuildMobileMessage(): string
    {
        return __p('core::phrase.please_rebuild_your_site_and_mobile_app');
    }

    /**
     * Check whether we should inform admin to rebuild mobile app.
     * @return bool
     */
    public function shouldAlertToRebuildMobile(): bool
    {
        return Settings::get('mobile.apple_app_id') || Settings::get('mobile.google_app_id');
    }
}
