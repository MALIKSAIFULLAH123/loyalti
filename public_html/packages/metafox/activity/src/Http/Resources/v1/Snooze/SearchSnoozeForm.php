<?php

namespace MetaFox\Activity\Http\Resources\v1\Snooze;

use MetaFox\Activity\Support\Constants;
use MetaFox\Activity\Support\Facades\Snooze;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

class SearchSnoozeForm extends AbstractForm
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
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(Snooze::getSearchSnoozeDesc()),
            Builder::tabs('type')
                ->label(__p('core::phrase.type'))
                ->options(Snooze::getSnoozeOptions()),
        );
    }
}
