<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use MetaFox\Subscription\Repositories\SubscriptionCancelReasonRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditSubscriptionCancelReasonForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditSubscriptionCancelReasonForm extends CreateSubscriptionCancelReasonForm
{
    protected function prepare(): void
    {
        $this->title(__p('subscription::admin.edit_reason'))
            ->action(apiUrl('admin.subscription.cancel-reason.update', [
                'cancel_reason' => $this->resource->entityId(),
            ]))
            ->asPut()
            ->setValue([
                'title' => Language::getPhraseValues($this->resource->title_var),
            ]);
    }

    public function boot(SubscriptionCancelReasonRepositoryInterface $repository, int $id): void
    {
        $this->resource = $repository->find($id);
    }
}
