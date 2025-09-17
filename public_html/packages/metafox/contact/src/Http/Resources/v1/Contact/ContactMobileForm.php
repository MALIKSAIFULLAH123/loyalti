<?php

namespace MetaFox\Contact\Http\Resources\v1\Contact;

use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Contact\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ContactMobileForm.
 */
class ContactMobileForm extends ContactForm
{
    protected function initialize(): void
    {
        $maxFullNameLength = 255;
        $maxSubjectLength  = 255;

        $this->addBasic()
            ->addFields(
                Builder::category('category_id')
                    ->label(__p('core::phrase.category'))
                    ->multiple(false)
                    ->required()
                    ->setRepository(CategoryRepositoryInterface::class)
                    ->valueType('number')
                    ->sx(['width' => 275])
                    ->yup(
                        Yup::number()->required()
                    ),
                Builder::text('full_name')
                    ->required()
                    ->marginNone()
                    ->label(__p('contact::phrase.full_name'))
                    ->maxLength($maxFullNameLength)
                    ->placeholder(__p('contact::phrase.full_name'))
                    ->yup(
                        Yup::string()
                            ->required()
                            ->maxLength($maxFullNameLength)
                    ),
                Builder::text('subject')
                    ->required()
                    ->marginNone()
                    ->label(__p('contact::phrase.subject'))
                    ->placeholder(__p('contact::phrase.subject'))
                    ->maxLength($maxSubjectLength)
                    ->yup(
                        Yup::string()
                            ->required()
                            ->maxLength($maxSubjectLength)
                    ),
                $this->handleEmailField(),
                $this->handleMessageField(),
                Builder::checkbox('send_copy')
                    ->label(__p('contact::phrase.send_yourself_a_copy')),
                Captcha::getFormField('contact.contact', 'mobile', true),
            );
    }

    protected function handleEmailField(): AbstractField
    {
        $maxEmailLength = 255;
        if ($this->isGuest) {
            return Builder::text('email')
                ->required()
                ->marginNormal()
                ->label(__p('core::phrase.email_address'))
                ->placeholder(__p('core::phrase.email_address'))
                ->maxLength($maxEmailLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->email(__p('validation.invalid_email_address'))
                        ->maxLength($maxEmailLength)
                );
        }

        return Builder::hidden('email');
    }

    protected function handleMessageField(): AbstractField
    {
        $setting = Settings::get('contact.allow_html_contact_form', true);
        if ($setting) {
            return Builder::richTextEditor('message')
                ->label(__p('core::phrase.message'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                );
        }

        return Builder::textArea('message')
            ->label(__p('core::phrase.message'))
            ->required()
            ->yup(
                Yup::string()
                    ->required()
            );
    }
}
