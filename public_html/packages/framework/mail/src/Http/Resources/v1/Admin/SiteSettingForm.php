<?php

namespace MetaFox\Mail\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Localize\Support\Traits\TranslatableSettingFieldTrait;
use MetaFox\Platform\Facades\Settings;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 */
class SiteSettingForm extends Form
{
    use TranslatableSettingFieldTrait {
        validated as validatedTrait;
    }

    private array $varsTranslatable;

    public function __construct()
    {
        parent::__construct();

        $this->varsTranslatable = [
            'mail.signature',
        ];
    }

    protected function prepare(): void
    {
        $vars = [
            'mail.from',
            'mail.queue',
            'mail.default',
            'mail.dns_check',
        ];

        $values = [];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        $this->getTranslatableValue($values);

        $this->title(__p('mail::phrase.mail_server_settings'))
            ->action('admincp/setting/mail')
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::checkbox('mail.queue')
                ->label(__p('mail::phrase.queue_label'))
                ->description(__p('mail::phrase.queue_desc')),
            Builder::selectMailTransport('mail.default')
                ->required()
                ->label(__p('mail::phrase.mail_send_method_label'))
                ->autoComplete('off')
                ->description(__p('mail::phrase.mail_send_method_desc')),
            Builder::text('mail.from.name')
                ->required()
                ->autoComplete('off')
                ->label(__p('mail::phrase.mail_from_label'))
                ->description(__p('mail::phrase.mail_from_desc'))
                ->placeholder('admin'),
            Builder::text('mail.from.address')
                ->required()
                ->autoComplete('off')
                ->label(__p('mail::phrase.mail_from_address_label'))
                ->description(__p('mail::phrase.mail_from_address_desc'))
                ->placeholder('name@your-domain.com'),
            Builder::translatableText('mail.signature')
                ->asTextArea()
                ->label(__p('mail::phrase.mail_signature_label'))
                ->description(__p('mail::phrase.mail_signature_desc'))
                ->buildFields(),
        );

        $this->addDefaultFooter(true);
    }
}
