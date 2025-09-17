<?php

namespace MetaFox\User\Http\Resources\v1\CancelReason\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\Html\Text;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\User\Models\CancelReason as Model;
use MetaFox\User\Repositories\CancelReasonAdminRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditCancelReasonForm.
 * @property ?Model $resource
 */
class EditCancelReasonForm extends CreateCancelReasonForm
{
    public function boot(CancelReasonAdminRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.update_cancel_reason'))
            ->action(apiUrl('admin.user.cancel-reason.update', ['cancel_reason' => $this->resource->entityId()]))
            ->asPut()
            ->setValue([
                'phrase_var' => Language::getPhraseValues($this->resource->phrase_var),
                'is_active'  => $this->resource->is_active,
            ]);
    }
}
