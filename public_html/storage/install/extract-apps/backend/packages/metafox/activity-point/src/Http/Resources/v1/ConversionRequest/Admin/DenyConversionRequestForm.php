<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\ActivityPoint\Models\ConversionRequest as Model;
use MetaFox\ActivityPoint\Policies\ConversionRequestPolicy;
use MetaFox\ActivityPoint\Repositories\ConversionRequestRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class DenyConversionRequestForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DenyConversionRequestForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('activitypoint::admin.deny_request'))
            ->action('admincp/activitypoint/conversion-request/' . $this->resource->entityId() . '/deny')
            ->asPatch();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::textArea('reason')
                    ->required()
                    ->label(__p('activitypoint::admin.please_give_the_reason_why_you_deny_this_request'))
                    ->yup(
                        Yup::string()
                            ->required(__p('activitypoint::validation.reason_is_required')),
                    ),
            );

        $this->addDefaultFooter();
    }

    public function boot(int $id): void
    {
        $this->resource = resolve(ConversionRequestRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(ConversionRequestPolicy::class, 'denyConversionRequest', $context, $this->resource);
    }
}
