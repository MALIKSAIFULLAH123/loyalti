<?php

namespace MetaFox\ActivityPoint\Support\Browse\Traits;

use Carbon\Carbon;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Yup\Yup;

/**
 * Trait DateFieldForSearchFromTrait.
 */
trait DateFieldForSearchFromTrait
{
    public ?User $owner = null;

    public function setOwner(?User $owner = null): self
    {
        $this->owner = $owner;

        return $this;
    }

    protected function buildFromField(): AbstractField
    {
        return Builder::date('from')
            ->forAdminSearchForm()
            ->label(__p('activitypoint::phrase.transaction_from'))
            ->maxDate(Carbon::now()->toISOString())
            ->startOfDay()
            ->sxFieldWrapper($this->getResponsiveSx())
            ->yup(
                Yup::date()
                    ->nullable()
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('activitypoint::phrase.transaction_from')]))
            );
    }

    protected function buildToField(): AbstractField
    {
        return Builder::date('to')
            ->forAdminSearchForm()
            ->label(__p('activitypoint::phrase.transaction_to'))
            ->maxDate(Carbon::now()->toISOString())
            ->endOfDay()
            ->sxFieldWrapper($this->getResponsiveSx())
            ->yup(
                Yup::date()
                    ->nullable()
                    ->min(['ref' => 'from'])
                    ->setError('min', __p('activitypoint::phrase.the_end_time_should_be_greater_than_the_start_time'))
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('activitypoint::phrase.transaction_to')]))
            );
    }

    protected function buildFromMobileField(): AbstractField
    {
        return MobileBuilder::date('from')
            ->forAdminSearchForm()
            ->label(__p('activitypoint::phrase.transaction_from'))
            ->maxDate(Carbon::now()->toISOString());
    }

    protected function buildToMobileField(): AbstractField
    {
        return MobileBuilder::date('to')
            ->forAdminSearchForm()
            ->label(__p('activitypoint::phrase.transaction_to'))
            ->maxDate(Carbon::now()->toISOString());
    }

    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width'    => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }
}
