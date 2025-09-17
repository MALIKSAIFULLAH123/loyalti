<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use Exception;
use MetaFox\Activity\Models\Feed as Model;
use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Activity\Support\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * Class SortFeedForm.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SortFeedForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('feed')
            ->asGet()
            ->submitAction('viewAll')
            ->submitOnValueChanged()
            ->acceptPageParams(['sort'])
            ->setValue([
                'sort' => Settings::get('activity.feed.sort_default', SortScope::SORT_DEFAULT),
            ]);
    }

    /**
     * @throws Exception
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::dropdown()
                ->name('sort')
                ->fullWidth()
                ->variant('outlined')
                ->className('select-sort-feed')
                ->disableClearable()
                ->marginNone()
                ->freeSolo(false)
                ->setAttribute('placement', 'right')
                ->sizeSmall()
                ->setAttribute('sxOptions', [
                    'fontSize'   => 15,
                    'fontWeight' => 600,
                ])
                ->setAttribute('disableScrollLock', true)
                ->sx([
                    'width' => 'auto',
                ])
                ->options(Support::getSortOptions()),
        );
    }
}
