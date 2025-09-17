<?php

namespace MetaFox\Mail\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Storage\Models\Asset;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 */
class AppearanceSettingForm extends Form
{
    private ?Asset $asset;

    private const ASSET_MAIL_LOGO_NAME = 'mail_logo';

    public function __construct()
    {
        parent::__construct();
        $this->asset = app('asset')->findByName(self::ASSET_MAIL_LOGO_NAME);
    }

    protected function prepare(): void
    {
        $vars = [
            'mail.test_email',
            'mail.enable_site_logo',
            'mail.enable_site_name',
            'mail.primary_background_color',
            'mail.content_background_color',
            'mail.content_text_color',
            'mail.button_background_color',
            'mail.button_text_color',
        ];

        $values = [];

        if ($this->asset instanceof Asset) {
            Arr::set($values, 'file', [
                'file_name' => basename($this->asset?->url),
                'file_type' => $this->asset?->file_mime_type,
                'url'       => $this->asset?->url,
            ]);
        }

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        $this->title(__p('mail::phrase.mail_server_settings'))
            ->action('admincp/setting/mail/appearance')
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::checkbox('mail.enable_site_name')
                ->label(__p('mail::phrase.enable_site_name_label')),
            Builder::checkbox('mail.enable_site_logo')
                ->label(__p('mail::phrase.enable_site_logo_label')),
            Builder::singlePhoto('file')
                ->placeholder(__p('core::phrase.upload'))
                ->label(__p('mail::phrase.upload_logo'))
                ->itemType('asset')
                ->uploadUrl('file')
                ->previewUrl($this->asset?->url)
                ->showWhen([
                    'and', ['truthy', 'mail.enable_site_logo'],
                ]),
            Builder::colorPicker('mail.content_background_color')
                ->width(400)
                ->label(__p('mail::phrase.mail_content_background_color_label')),
            Builder::colorPicker('mail.primary_background_color')
                ->width(400)
                ->label(__p('mail::phrase.mail_primary_background_color_label')),
            Builder::colorPicker('mail.content_text_color')
                ->width(400)
                ->label(__p('mail::phrase.mail_content_text_color_label')),
            Builder::colorPicker('mail.button_background_color')
                ->width(400)
                ->label(__p('mail::phrase.mail_button_background_color_label')),
            Builder::colorPicker('mail.button_text_color')
                ->width(400)
                ->label(__p('mail::phrase.mail_button_text_color_label')),
            Builder::text('mail.test_email')
                ->width(400)
                ->autoComplete('off')
                ->label('Test Email'),
        );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request): array
    {
        $this->upload($this->asset, $request);
        $params = $request->all();
        Arr::forget($params, 'file');

        $mailable = new Mailable();

        $mailable->subject('Verify Config')
            ->line('This is test mail configuration.')
            ->view('html')
            ->action(__p('core::phrase.view_now'), config('app.url'));

        Mail::to(Arr::get($params, 'mail.test_email'))->send($mailable);

        return $params;
    }

    public function upload(?Asset $asset, Request $request): void
    {
        $file = $request->get('file', null);
        if (!$file) {
            return;
        }

        if (!Arr::has($file, 'temp_file')) {
            return;
        }

        if (!$asset instanceof Asset) {
            $asset = new Asset();
            $asset->fill([
                'module_id'  => 'mail',
                'package_id' => 'metafox/mail',
                'name'       => self::ASSET_MAIL_LOGO_NAME,
                'local_path' => '',
            ]);
        }

        $asset->file_id = Arr::get($file, 'temp_file');

        $asset->save();

        $asset->refresh();

        app('events')->dispatch('storage.asset.uploaded', [$asset]);
    }
}
