<?php

namespace MetaFox\Group\Http\Resources\v1\Request;

use Carbon\Carbon;
use MetaFox\Group\Models\Request as Model;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchRequestMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchRequestMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('group::phrase.search_requests'))
            ->action('group-request')
            ->acceptPageParams(['q', 'view', 'status', 'start_date', 'end_date'])
            ->setValue([
                'view'       => Browse::VIEW_ALL,
                'start_date' => null,
                'end_date'   => null,
            ])
            ->asGet();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])
            ->showWhen(['falsy', 'filters']);

        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('group::phrase.search_requests')),
            Builder::button('filters')
                ->forBottomSheetForm(),
        );

        $this->getBasicFields($basic);

        $bottomSheet = $this->addSection(['name' => 'bottomSheet']);

        $this->getBottomSheetFields($bottomSheet);
    }

    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            $this->getClearSearchFieldsFlatten()
                ->targets(['start_date', 'end_date', 'status']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('group::phrase.search_requests')),
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->placeholder(__p('core::phrase.status'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->options(StatusScope::getStatusOptions()),
            Builder::date('start_date')
                ->label(__p('core::web.from'))
                ->placeholder(__p('core::web.from'))
                ->startOfDay()
                ->maxDate(Carbon::now()->toISOString())
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->forBottomSheetForm(),
            Builder::date('end_date')
                ->label(__p('core::phrase.to_label'))
                ->placeholder(__p('core::phrase.to_label'))
                ->maxDate(Carbon::now()->toISOString())
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->forBottomSheetForm(),
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['status', 'start_date', 'end_date'])
                ->showWhen(['truthy', 'filters']),
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->placeholder(__p('core::phrase.status'))
                ->forBottomSheetForm()
                ->autoSubmit()
                ->showWhen(['truthy', 'filters'])
                ->variant('standard-inlined')
                ->options(StatusScope::getStatusOptions()),
            Builder::date('start_date')
                ->label(__p('core::web.from'))
                ->forBottomSheetForm()
                ->placeholder(__p('core::web.from'))
                ->variant('standard')
                ->maxDate(Carbon::now()->toISOString())
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->showWhen(['truthy', 'filters']),
            Builder::date('end_date')
                ->label(__p('core::phrase.to_label'))
                ->forBottomSheetForm()
                ->maxDate(Carbon::now()->toISOString())
                ->placeholder(__p('core::phrase.to_label'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->variant('standard')
                ->showWhen(['truthy', 'filters']),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }
}
