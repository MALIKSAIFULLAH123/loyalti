<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName live_video.search_in_owner
 * @driverType form
 * @preload    1
 */
class SearchInOwnerMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('live-video')
            ->acceptPageParams(['q'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->delayTime(200)
                ->placeholder(__p('livestreaming::phrase.search_live_videos'))
                ->className('mb2'),
        );
    }
}
