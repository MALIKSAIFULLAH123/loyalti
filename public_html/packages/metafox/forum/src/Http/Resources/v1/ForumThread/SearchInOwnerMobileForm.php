<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName forum_thread.search_in_owner
 * @driverType form
 * @preload    1
 */
class SearchInOwnerMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('forum-thread')
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
                ->placeholder(__p('forum::web.search_discussions'))
                ->className('mb2'),
        );
    }
}
