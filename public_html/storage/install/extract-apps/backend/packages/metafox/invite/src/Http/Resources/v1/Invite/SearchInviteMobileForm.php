<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Invite\Support\Facades\Invite as InviteFacade;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchInviteMobileForm.
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInviteMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/invite/search')
            ->acceptPageParams(['q', 'status'])
            ->setValue([
                'status' => Browse::VIEW_ALL,
                'view'   => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);
        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('invite::phrase.emails_phone_numbers')),
            Builder::button('filters')
                ->forBottomSheetForm(),
        );

        $basic->addFields(
            Builder::choice('status')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('invite::phrase.status'))
                ->options(InviteFacade::getStatusOptions()),
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
                ->targets(['status', 'start_date', 'end_date']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('invite::phrase.emails_phone_numbers')),
            Builder::choice('status')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('invite::phrase.status'))
                ->options(InviteFacade::getStatusOptions()),
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::date('start_date')
                ->label(__p('core::web.from'))
                ->placeholder(__p('core::web.from'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->forBottomSheetForm(),
            Builder::date('end_date')
                ->label(__p('core::phrase.to_label'))
                ->placeholder(__p('core::phrase.to_label'))
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
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->autoSubmit()
                ->label(__p('invite::phrase.status'))
                ->showWhen(['truthy', 'filters'])
                ->options(InviteFacade::getStatusOptions()),
            Builder::date('start_date')
                ->label(__p('core::web.from'))
                ->placeholder(__p('core::web.from'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters']),
            Builder::date('end_date')
                ->label(__p('core::phrase.to_label'))
                ->placeholder(__p('core::phrase.to_label'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters']),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }
}
