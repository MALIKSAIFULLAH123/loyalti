<?php

namespace MetaFox\Report\Http\Resources\v1\ReportReason\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Report\Models\ReportReason as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateReportReasonForm.
 * @property ?Model $resource
 */
class CreateReportReasonForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('report::phrase.add_new_reason'))
            ->action('/admincp/report/reason')
            ->asPost()
            ->setValue([
                'locale'     => 'en',
                'package_id' => 'report',
                'group'      => 'phrase',
                'text'       => '',
                'is_custom'  => 1,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::translatableText('name')
                ->label(__p('core::phrase.name'))
                ->required()
                ->buildFields(),
        );

        $this->addDefaultFooter();
    }
}
