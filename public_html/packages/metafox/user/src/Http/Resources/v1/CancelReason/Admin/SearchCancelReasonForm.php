<?php

namespace MetaFox\User\Http\Resources\v1\CancelReason\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\CancelReason as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchCancelReasonForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchCancelReasonForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action(apiUrl('admin.user.cancel-reason.index'))
            ->acceptPageParams(['q', 'is_active'])
            ->asGet()
            ->setValue([
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::choice('is_active')
                    ->forAdminSearchForm()
                    ->label(__p('app::phrase.is_active'))
                    ->options($this->getYesNoOptions()),
                Builder::submit()->forAdminSearchForm(),
            );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getYesNoOptions(): array
    {
        return [
            ['label' => __p('core::phrase.yes'), 'value' => 1],
            ['label' => __p('core::phrase.no'), 'value' => 0],
        ];
    }
}
