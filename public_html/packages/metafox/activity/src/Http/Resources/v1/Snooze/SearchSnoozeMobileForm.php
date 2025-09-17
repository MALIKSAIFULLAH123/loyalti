<?php

namespace MetaFox\Activity\Http\Resources\v1\Snooze;

use MetaFox\Activity\Support\Constants;
use MetaFox\Activity\Support\Facades\Snooze;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;

class SearchSnoozeMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('feed/snooze')
            ->acceptPageParams(['q', 'type'])
            ->setValue([
                'type' => Constants::SNOOZE_TYPE_USER,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->delayTime(200)
                ->placeholder(Snooze::getSearchSnoozeDesc())
                ->className('mb2'),
        );

        $this->getBasicFields($basic);
    }

    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            $this->getSearchFieldsFlatten()
                ->placeholder(Snooze::getSearchSnoozeDesc())
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $viewSection = $this
            ->addSection(['name' => 'viewSection', 'paddingBottom' => 'none'])
            ->showWhen(['falsy', 'filters']);

        $viewSection->addFields(
            Builder::choice('type')
                ->forBottomSheetForm('SFTabSelect')
                ->autoSubmit()
                ->label(__p('core::phrase.type'))
                ->options(Snooze::getSnoozeOptions()),
        );
    }
}
