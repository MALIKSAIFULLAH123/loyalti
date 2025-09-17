<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise;

use MetaFox\Advertise\Http\Resources\v1\Advertise\Admin\CreateAdvertiseForm as AdminForm;
use MetaFox\Advertise\Policies\AdvertisePolicy;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Support\Form\Html\AdvertiseCalculatorCost;
use MetaFox\Form\Section;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateAdvertiseForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateAdvertiseForm extends AdminForm
{
    protected function prepare(): void
    {
        parent::prepare();

        $values = $this->getValue();

        unset($values['total_click']);

        unset($values['total_impression']);

        $this->action('advertise/advertise')
            ->title(__p('advertise::phrase.create_new_ad'))
            ->setValue($values);
    }

    protected function addEndDateField(Section $section): void
    {
    }

    protected function addTotalFields(Section $section): void
    {
        $clickPlacements      = array_combine(array_column($this->availableClickPlacements, 'value'), $this->availableClickPlacements);
        $impressionPlacements = array_combine(array_column($this->availableImpressionPlacements, 'value'), $this->availableImpressionPlacements);

        $section->addFields(
            (new AdvertiseCalculatorCost())
                ->name('total_click')
                ->relatedInitialPrice('placement_id')
                ->placementOptions($clickPlacements)
                ->requiredWhen([
                    'includes',
                    'placement_id',
                    $this->availableClickPlacementIds,
                ])
                ->showWhen([
                    'includes',
                    'placement_id',
                    $this->availableClickPlacementIds,
                ])
                ->label(__p('advertise::phrase.total_clicks'))
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('placement_id')
                                ->is(
                                    Yup::number()
                                        ->oneOf($this->availableClickPlacementIds)
                                        ->toArray()
                                )
                                ->then(
                                    Yup::number()
                                        ->required(__p('advertise::validation.total_clicks_is_a_required_field'))
                                        ->min(1, __p('advertise::validation.total_clicks_must_be_greater_than_or_equal_to_number', ['number' => 1]))
                                        ->max(Support::MAX_TOTAL_NUMBER, __p('advertise::validation.total_clicks_must_be_less_than_or_equal_to_number', ['number' => number_format(Support::MAX_TOTAL_NUMBER)]))
                                        ->unint(__p('advertise::validation.total_clicks_must_be_number'))
                                        ->setError('typeError', __p('advertise::validation.total_clicks_must_be_number'))
                                )
                        ),
                ),
            (new AdvertiseCalculatorCost())
                ->name('total_impression')
                ->relatedInitialPrice('placement_id')
                ->initialUnit(1000)
                ->setAttribute('relatedPlacementType', Support::PLACEMENT_CPM)
                ->placementOptions($impressionPlacements)
                ->requiredWhen([
                    'includes',
                    'placement_id',
                    $this->availableImpressionPlacementIds,
                ])
                ->showWhen([
                    'includes',
                    'placement_id',
                    $this->availableImpressionPlacementIds,
                ])
                ->label(__p('advertise::phrase.total_impressions'))
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('placement_id')
                                ->is(
                                    Yup::number()
                                        ->oneOf($this->availableImpressionPlacementIds)
                                        ->toArray()
                                )
                                ->then(
                                    Yup::number()
                                        ->required(__p('advertise::validation.total_impressions_is_a_required_field'))
                                        ->min(100, __p('advertise::validation.total_impressions_must_be_greater_than_or_equal_to_number', ['number' => 100]))
                                        ->max(Support::MAX_TOTAL_NUMBER, __p('advertise::validation.total_impressions_must_be_less_than_or_equal_to_number', ['number' => number_format(Support::MAX_TOTAL_NUMBER)]))
                                        ->unint(__p('advertise::validation.total_impressions_must_be_number'))
                                        ->setError('typeError', __p('advertise::validation.total_impressions_must_be_number'))
                                )
                        ),
                )
        );
    }

    protected function addActiveField(Section $section): void
    {
    }

    public function boot(): void
    {
        $context = user();

        policy_authorize(AdvertisePolicy::class, 'create', $context);
    }

    protected function isAdminCP(): bool
    {
        return false;
    }
}
