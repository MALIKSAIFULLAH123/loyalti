<?php

namespace MetaFox\Contact\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CommaSeparatedEmailsRule;
use MetaFox\Yup\Yup;

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
        $module = 'contact';
        $vars   = [
            'contact.staff_emails',
            'contact.enable_auto_responder',
            'contact.allow_html_contact_form',
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
        $this->addBasic()
            ->addFields(
                Builder::text('contact.staff_emails')
                    ->label(__p('contact::admin.staff_emails_title'))
                    ->description(__p('contact::admin.staff_emails_description'))
                    ->required()
                    ->autoComplete('off')
                    ->yup(
                        Yup::string()
                            ->required()
                            ->setError('required', __p('validation.this_field_is_a_required_field'))
                            ->setError('typeError', __p('validation.string', [
                                'attribute' => __p('contact::admin.staff_emails_title'),
                            ])),
                    ),
                Builder::switch('contact.enable_auto_responder')
                    ->label(__p('contact::admin.enable_auto_responder_title'))
                    ->description(__p('contact::admin.enable_auto_responder_desc'))
                    ->required(),
                Builder::switch('contact.allow_html_contact_form')
                    ->label(__p('contact::admin.allow_html_in_contact_form_title'))
                    ->description(__p('contact::admin.allow_html_in_contact_form_desc'))
                    ->required(),
            );

        $this->addDefaultFooter(true);
    }

    /**
     * validated.
     *
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        return $request->validate([
            'contact.staff_emails'            => ['sometimes', 'string', new CommaSeparatedEmailsRule()],
            'contact.enable_auto_responder'   => ['sometimes', 'int', new AllowInRule([0, 1])],
            'contact.allow_html_contact_form' => ['sometimes', 'int', new AllowInRule([0, 1])],
        ]);
    }
}
