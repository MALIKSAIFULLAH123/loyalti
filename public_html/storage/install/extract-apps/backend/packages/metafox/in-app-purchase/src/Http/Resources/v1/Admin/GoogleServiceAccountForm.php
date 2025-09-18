<?php

namespace MetaFox\InAppPurchase\Http\Resources\v1\Admin;

use Illuminate\Support\Str;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\InAppPurchase\Repositories\Eloquent\GoogleServiceAccountRepository;
use MetaFox\Platform\Facades\Settings;

/**
 * Class GoogleServiceAccountForm.
 * @codeCoverageIgnore
 * @ignore
 */
class GoogleServiceAccountForm extends Form
{
    protected function prepare(): void
    {
        $apiKey     = Settings::get(GoogleServiceAccountRepository::SETTING_NAME);
        $keyLength  = mb_strlen($apiKey);
        $apiKeyDesc = $apiKey ? Str::mask($apiKey, '*', -$keyLength, $keyLength - 18) : '';
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.in-app-purchase.google-service-account.store'))
            ->asMultipart()
            ->setValue([
                'path' => $apiKeyDesc,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::typography('description')
                    ->plainText(__p('in-app-purchase::phrase.google_service_account_key_description', [
                        'link'      => 'https://developers.google.com/android-publisher/getting_started',
                        'link_play' => 'https://play.google.com/apps/publish/',
                    ])),
                Builder::rawFile('file')
                    ->accepts('.json')
                    ->maxUploadSize(20000)
                    ->description(__p('in-app-purchase::phrase.service_file_description'))
                    ->label(__p('in-app-purchase::phrase.google_service_account_key_file_json'))
                    ->placeholder(__p('in-app-purchase::phrase.attach_file')),
            );
        $this->addSection('service_path')
            ->addFields(
                Builder::text('path')
                    ->readOnly()
                    ->disabled()
                    ->showWhen(['truthy', 'path'])
                    ->label(__p('in-app-purchase::phrase.current_google_account_key_file'))
            );
        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.submit')),
            );
    }
}
