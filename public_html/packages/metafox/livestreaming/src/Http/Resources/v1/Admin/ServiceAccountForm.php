<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\Admin;

use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\LiveStreaming\Repositories\Eloquent\ServiceAccountRepository;
use MetaFox\Platform\Facades\Settings;

/**
 * Class ServiceAccountForm.
 * @codeCoverageIgnore
 * @ignore
 */
class ServiceAccountForm extends Form
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.livestreaming.service-account.store'))
            ->asMultipart()
            ->setValue([
                'path' => Settings::get(ServiceAccountRepository::SETTING_NAME),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::typography('description')
                    ->plainText(__p('livestreaming::phrase.service_account_key_description', [
                        'firebaseLink' => 'https://console.firebase.google.com/',
                        'googleLink'   => 'https://console.cloud.google.com/apis/credentials/serviceaccountkey',
                    ])),
                Builder::rawFile('file')
                    ->accepts('.json')
                    ->maxUploadSize(20000)
                    ->description('')
                    ->label(__p('livestreaming::phrase.service_account_key_file_json'))
                    ->placeholder(__p('livestreaming::phrase.attach_file')),
            );
        if (Settings::get(ServiceAccountRepository::SETTING_NAME)) {
            $this->addSection('service_path')
                ->addFields(
                    Builder::text('path')
                        ->readOnly()
                        ->label(__p('livestreaming::phrase.current_account_key_file'))
                );
        }
        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.submit')),
            );
    }
}
