<?php

namespace MetaFox\Group\Http\Resources\v1\Invite;

use Carbon\Carbon;
use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Group\Models\Invite as Model;
use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\ViewScope;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchInviteForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInviteMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('group-invite')
            ->acceptPageParams(['view', 'status', 'q', 'created_from', 'created_to'])
            ->setValue([
                'view'         => ViewScope::VIEW_ALL,
                'status'       => null,
                'created_from' => null,
                'created_to'   => null,
            ])
            ->asGet();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);
        $basic->addFields(
            $this->getSearchFields()
                ->placeholder(__p('group::phrase.search_group_invite_placeholder')),
            Builder::button('filters')
                ->forBottomSheetForm(),
        );

        $basic->addFields(
            Builder::choice('view')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::phrase.role'))
                ->options(ViewScope::getViewOptions()),
            Builder::choice('status')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::web.status'))
                ->options(StatusScope::getStatusOptions()),
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
                ->targets(['status', 'q', 'view', 'created_from', 'created_to']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('group::phrase.search_group_invite_placeholder')),
            Builder::choice('view')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::phrase.role'))
                ->options(ViewScope::getViewOptions()),
            Builder::choice('status')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::web.status'))
                ->options(StatusScope::getStatusOptions()),
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(
            Builder::date('created_from')
                ->label(__p('group::phrase.created_from_label'))
                ->placeholder(__p('group::phrase.created_from_label'))
                ->startOfDay()
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->maxDate(Carbon::now()->toISOString())
                ->forBottomSheetForm(),
            Builder::date('created_to')
                ->label(__p('group::phrase.created_to_label'))
                ->endOfDay()
                ->placeholder(__p('group::phrase.created_to_label'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->maxDate(Carbon::now()->toISOString())
                ->forBottomSheetForm(),
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->targets(['view', 'status', 'q', 'created_from', 'created_to'])
                ->showWhen(['truthy', 'filters']),
            Builder::choice('view')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->autoSubmit()
                ->label(__p('core::phrase.role'))
                ->showWhen(['truthy', 'filters'])
                ->options(ViewScope::getViewOptions()),
            Builder::choice('status')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->autoSubmit()
                ->label(__p('core::web.status'))
                ->showWhen(['truthy', 'filters'])
                ->options(StatusScope::getStatusOptions()),
            Builder::date('created_from')
                ->label(__p('group::phrase.created_from_label'))
                ->placeholder(__p('group::phrase.created_from_label'))
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->startOfDay()
                ->maxDate(Carbon::now()->toISOString())
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters']),
            Builder::date('created_to')
                ->label(__p('group::phrase.created_to_label'))
                ->placeholder(__p('group::phrase.created_to_label'))
                ->maxDate(Carbon::now()->toISOString())
                ->endOfDay()
                ->setAttribute('displayFormat', MetaFoxConstant::DISPLAY_FORMAT_TIME)
                ->forBottomSheetForm()
                ->showWhen(['truthy', 'filters']),
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
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
