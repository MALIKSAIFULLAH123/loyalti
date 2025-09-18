<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\Section;
use MetaFox\Subscription\Models\SubscriptionComparison as Model;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditSubscriptionComparisonForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditSubscriptionComparisonForm extends CreateSubscriptionComparisonForm
{
    protected function prepare(): void
    {
        $values = [
            'title' => $this->resource->title,
        ];

        $packages = $this->resource->packages;

        if (null !== $packages && $packages->count()) {
            foreach ($packages as $package) {
                $type = $package->type;

                $varName = 'package_' . $package->pivot->package_id . '_';

                Arr::set($values, $varName . 'type', $type);

                if ($type == Helper::COMPARISON_TYPE_TEXT && null !== $package->value) {
                    Arr::set($values, $varName . 'text', $package->value);
                }
            }
        }

        $this->title(__p('subscription::admin.edit_comparison_feature'))
            ->action(apiUrl('admin.subscription.comparison.update', [
                'comparison' => $this->resource->entityId(),
            ]))
            ->asPut()
            ->setValue($values);
    }

    protected function addFooterFields(Section $footer): void
    {
        $this->addDefaultFooter(true);
    }

    /**
     * @param  SubscriptionComparisonRepositoryInterface $repository
     * @param  int|null                                  $comparison
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(SubscriptionComparisonRepositoryInterface $repository, ?int $comparison = null): void
    {
        $this->resource = $repository->find($comparison);
    }
}
