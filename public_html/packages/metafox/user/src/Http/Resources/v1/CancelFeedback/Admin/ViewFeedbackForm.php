<?php

namespace MetaFox\User\Http\Resources\v1\CancelFeedback\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Models\CancelFeedback as Model;
use MetaFox\User\Repositories\CancelFeedbackAdminRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ViewFeedbackForm.
 *
 * @property ?Model $resource
 */
class ViewFeedbackForm extends AbstractForm
{
    public function boot(CancelFeedbackAdminRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title($this->resource->reason->title ?? __p('user::phrase.reason'))
            ->action('')
            ->asGet()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::description('description')
                ->setAttribute('descriptionProps', [
                    'color' => 'text.primary',
                ])
                ->description($this->resource->feedback_text)
        );

    }

    /**
     * @return bool
     */
    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId();
    }
}
