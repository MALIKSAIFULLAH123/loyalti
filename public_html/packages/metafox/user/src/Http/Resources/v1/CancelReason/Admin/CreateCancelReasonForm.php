<?php

namespace MetaFox\User\Http\Resources\v1\CancelReason\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Html\Text;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\User\Models\CancelReason as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateCancelReasonForm.
 * @property ?Model $resource
 */
class CreateCancelReasonForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('user::phrase.add_new_reason'))
            ->action('/admincp/user/cancel-reason')
            ->asPost()
            ->setValue([
                'is_active' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::translatableText('phrase_var')
                ->required()
                ->label(__p('user::phrase.cancel_reason'))
                ->buildFields(),
        );

        if (!$this->isEdit()) {
            $basic->addFields(
                Builder::checkbox('is_active')
                    ->label(__p('core::phrase.is_active')),
            );
        }

        $this->addDefaultFooter($this->isEdit());
    }

    /**
     * @return bool
     */
    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId();
    }
}
