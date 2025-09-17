<?php

namespace MetaFox\Contact\Http\Resources\v1\Contact;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Contact\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ContactForm.
 */
class ContactForm extends AbstractForm
{
    protected User $context;
    protected bool $isGuest;

    /**
     * @param null $resource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function __construct($resource = null)
    {
        parent::__construct($resource);

        $this->context = user();
        $this->isGuest = $this->context->isGuest();

        if (!$this->context->hasPermissionTo('contact.create')) {
            throw new AuthorizationException();
        }
    }

    /**
     * @return void
     */
    protected function prepare(): void
    {
        $values = [
            'subject'   => MetaFoxConstant::EMPTY_STRING,
            'full_name' => MetaFoxConstant::EMPTY_STRING,
        ];

        if (!$this->isGuest) {
            Arr::set($values, 'email', $this->context->email);
        }

        $defaultCategory = Settings::get('contact.default_category');
        Arr::set($values, 'category_id', $defaultCategory);

        $this->title(__p('contact::phrase.contact_form_title'))
            ->description(__p('contact::phrase.contact_form_description'))
            ->action(apiUrl('contact.store'))
            ->resetFormOnSuccess()
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $maxFullNameLength = 255;
        $maxSubjectLength  = 255;
        $basic             = $this->addBasic();

        $basic->addFields(
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
                ->marginNormal()
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
                ->marginNormal()
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
            Captcha::getFormField('contact.contact', 'web', true),
        );

        $this->addDefaultFooter();
    }

    /**
     * @return AbstractField
     */
    protected function handleEmailField(): AbstractField
    {
        $maxEmailLength = 255;

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

    protected function handleMessageField(): AbstractField
    {
        $setting = Settings::get('contact.allow_html_contact_form', true);
        if ($setting) {
            return Builder::richTextEditor('message')
                ->label(__p('core::phrase.message'))
                ->placeholder(__p('core::phrase.enter_your_message'))
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
