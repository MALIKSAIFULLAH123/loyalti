<?php

namespace MetaFox\InAppPurchase\Http\Resources\v1\Gateway\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Form\Section;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Payment\Http\Resources\v1\Gateway\Admin\GatewayForm as AdminGatewayForm;
use MetaFox\Payment\Models\Gateway as Model;
use MetaFox\Platform\Facades\Settings;

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
        if (empty($this->resource)) {
            return;
        }
        $vars   = [
            'in-app-purchase.enable_iap_ios',
            'in-app-purchase.enable_iap_android',
            'in-app-purchase.enable_iap_sandbox_mode',
            'in-app-purchase.google_android_package_name',
            'in-app-purchase.apple_app_id',
            'in-app-purchase.apple_key_id',
            'in-app-purchase.apple_issuer_id',
            'in-app-purchase.apple_private_key',
            'in-app-purchase.apple_bundle_id',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('payment::phrase.edit_payment_gateway'))
            ->action(apiUrl('admin.in-app-purchase.gateway.update', ['gateway' => $this->resource->id]))
            ->asPut();

        if ($this->resource instanceof Model) {
            $this->setValue(array_merge([
                'title'       => $this->resource->title,
                'description' => $this->resource->description,
                'is_test'     => $this->resource->is_test,
                'is_active'   => $this->resource->is_active,
            ], $this->resource->config, $value));
        }
    }

    /**
     * @return array|FormField[]
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function getGatewayConfigFields(): array
    {
        return [
            ...InAppPur::getSettingFormFields(),
            Builder::linkButton('manage_processors')
                ->link('/in-app-purchase/product/browse')
                ->variant('link')
                ->sizeNormal()
                ->color('primary')
                ->setAttribute('controlProps', ['sx' => ['display' => 'block']])
                ->label(__p('in-app-purchase::phrase.manage_products')),
        ];
    }

    protected function handleFieldIsTest(Section $basic): AbstractField
    {
        return $basic->addField(
            Builder::hidden('is_test')
                ->label(__p('payment::phrase.is_test'))
        );
    }
}
