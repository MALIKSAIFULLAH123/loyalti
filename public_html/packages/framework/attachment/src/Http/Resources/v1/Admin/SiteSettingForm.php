<?php

namespace MetaFox\Attachment\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

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
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'core';

        $vars = [
            'core.attachment.maximum_number_of_attachments_that_can_be_uploaded',
            'core.attachment.maximum_file_size_each_attachment_can_be_uploaded',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }
        $this->asPost()
            ->title(__p('core::phrase.attachment_settings'))
            ->action('admincp/setting/' . $module)
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('core.attachment.maximum_number_of_attachments_that_can_be_uploaded')
                    ->required()
                    ->asNumber()
                    ->label(__p('core::phrase.maximum_number_of_attachments_that_can_be_uploaded'))
                    ->yup(Yup::number()->unint()->required()),
                Builder::text('core.attachment.maximum_file_size_each_attachment_can_be_uploaded')
                    ->label(__p('core::phrase.maximum_file_size_each_attachment_can_be_uploaded'))
                    ->description(__p('core::phrase.maximum_file_size_each_attachment_can_be_uploaded_desc'))
                    ->required()
                    ->yup(
                        Yup::number()
                            ->required()
                            ->int()
                            ->min(0)
                            ->setError('typeError', __p('attachment::phrase.maximum_file_size_must_be_number'))
                    )
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()
            );
    }
}
