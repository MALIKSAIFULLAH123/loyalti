<?php

namespace MetaFox\InAppPurchase\Http\Resources\v1\Product\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Importer\Models\Bundle as Model;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchBundleForm.
 * @property Model $resource
 */
class SearchProductForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/admincp/in-app-purchase/product/browse')
            ->acceptPageParams(['type'])
            ->title('')
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $productTypes = InAppPur::getProductTypes();
        if (!$productTypes) {
            return;
        }
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.search')),
                Builder::choice('item_type')
                    ->forAdminSearchForm()
                    ->label(__p('in-app-purchase::admin.product_type'))
                    ->options($productTypes),
                Builder::submit()
                    ->forAdminSearchForm(),
            );
    }
}
