<?php

namespace MetaFox\Paypal\Http\Resources\v1\Gateway\Admin;

use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Payment\Http\Resources\v1\Gateway\Admin\GatewayForm as AdminGatewayForm;
use MetaFox\Payment\Models\Gateway as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class GatewayForm.
 * @property Model $resource
 */
class GatewayForm extends AdminGatewayForm
{
    public function prepare(): void
    {
        // TODO: for security reason, should prevent client_secret from populating
        parent::prepare();
    }
    /**
     * getGatewayConfigFields.
     *
     * @return array<FormField>
     */
    protected function getGatewayConfigFields(): array
    {
        return [
            Builder::text('client_id')
                ->required()
                ->label(__p(('paypal::admin.client_id')))
                ->yup(
                    Yup::string()->required(__p('validation.this_field_is_a_required_field'))
                ),
            Builder::text('client_secret')
                ->required()
                ->label(__p(('paypal::admin.client_secret')))
                ->yup(
                    Yup::string()->required(__p('validation.this_field_is_a_required_field'))
                ),
            Builder::text('webhook_id')
                ->required()
                ->label(__p(('paypal::admin.webhook_id')))
                ->yup(
                    Yup::string()->required(__p('validation.this_field_is_a_required_field'))
                ),
            Builder::typography('webhook_id_description')
                ->plainText(__p(('paypal::admin.webhook_id_description'))),
            Builder::text('return_url_on_success')
                ->label(__p('payment::admin.return_url_after_successful_payment'))
                ->description(__p('payment::admin.return_url_description', ['gateway' => $this->resource?->title ?? '']))
                ->yup(
                    Yup::string(),
                ),
            Builder::text('return_url_on_cancelled')
                ->label(__p('payment::admin.return_url_after_cancelled_payment'))
                ->description(__p('payment::admin.return_url_after_cancelled_payment_description', ['gateway' => $this->resource?->title ?? '']))
                ->yup(
                    Yup::string(),
                ),
        ];
    }

    protected function getValidationRules(): array
    {
        return array_merge(parent::getValidationRules(), [
            'client_id'     => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'webhook_id'    => ['required', 'string'],
            'return_url_on_success'    => ['sometimes', 'nullable', 'string'],
            'return_url_on_cancelled'    => ['sometimes', 'nullable', 'string'],
        ]);
    }
}
