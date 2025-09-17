<?php

namespace MetaFox\Rewrite\Http\Resources\v1\Rule\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Rewrite\Models\Rule as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreRuleForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreRuleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('core::phrase.edit'))
            ->action('/admincp/rewrite/rule')
            ->asPost()
            ->setValue([
                //
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('from_path')
                ->required()
                ->label('From Path')
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('to_path')
                ->required()
                ->label('To Path')
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('to_mobile_path')
                ->required()
                ->label('To Mobile Path')
                ->yup(
                    Yup::string()
                        ->required()
                ),
        );

        $this->addDefaultFooter();
    }
}
