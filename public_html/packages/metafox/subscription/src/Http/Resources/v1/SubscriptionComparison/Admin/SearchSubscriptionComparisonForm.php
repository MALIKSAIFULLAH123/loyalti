<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchSubscriptionPackageForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchSubscriptionComparisonForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('subscription::admin.manage_comparisons'));
    }
}
