<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Livestreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\DurationScope;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName live_video.search
 * @driverType form
 * @preload    1
 */
class SearchLiveVideoForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/live-video/search')
            ->acceptPageParams(['q', 'sort', 'when', 'returnUrl', 'streaming', 'duration', 'is_featured'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('livestreaming::phrase.search_live_videos'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['view', 'q']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->sizeLarge()
                ->options([['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_LATEST], ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED], ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED], ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED]]),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->sizeLarge()
                ->options([['label' => __p('core::phrase.when.all'), 'value' => Browse::WHEN_ALL], ['label' => __p('core::phrase.when.this_month'), 'value' => Browse::WHEN_THIS_MONTH], ['label' => __p('core::phrase.when.this_week'), 'value' => Browse::WHEN_THIS_WEEK], ['label' => __p('core::phrase.when.today'), 'value' => Browse::WHEN_TODAY]]),
        );
        if (DurationScope::getSetting() > 0) {
            $basic->addFields(
                Builder::choice('duration')
                    ->label(__p('livestreaming::phrase.duration'))
                    ->marginNormal()
                    ->sizeLarge()
                    ->options([['label' => __p('livestreaming::phrase.longer_than_minutes', ['value' => DurationScope::getSetting()]), 'value' => DurationScope::DURATION_LONGER], ['label' => __p('livestreaming::phrase.shorter_than_minutes', ['value' => DurationScope::getSetting()]), 'value' => DurationScope::DURATION_SHORTER]]),
            );
        }
        $basic->addFields(
            Builder::switch('streaming')
                ->label(__p('livestreaming::phrase.live')),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
        );
    }
}
