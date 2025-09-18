<?php

namespace MetaFox\InAppPurchase\Http\Resources\v1\Product\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\InAppPurchase\Models\Product as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateProductForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateProductForm extends AbstractForm
{
    protected function prepare(): void
    {
        $value = $this->resource->toArray();
        $this->title(__p('core::phrase.edit'))
            ->action(url_utility()->makeApiUrl('admincp/in-app-purchase/product/' . $this->resource->id))
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $isRecurring = $this->resource->is_recurring;
        $type        = $this->resource->item_type;
        $enableInApp = !$isRecurring || $type !== 'subscription_package';
        $this->addBasic()
            ->addFields(
                Builder::text('title')
                    ->label(__p('core::phrase.title'))
                    ->disabled($enableInApp)
                    ->readOnly(),
                Builder::divider()
                    ->sx([
                        'mt' => 1,
                        'mb' => 1,
                    ]),
                Builder::text('ios_product_id')
                    ->disabled($enableInApp)
                    ->label(__p('in-app-purchase::phrase.ios_product_id'))
                    ->yup(Yup::string()),
                Builder::text('android_product_id')
                    ->label(__p('in-app-purchase::phrase.android_product_id'))
                    ->disabled($enableInApp)
                    ->yup(Yup::string()),
                Builder::typography('product_description')
                    ->color('text.secondary')
                    ->plainText(__p('in-app-purchase::phrase.in_app_product_notice')),
            );

        $this->addDefaultFooter();
    }
}
