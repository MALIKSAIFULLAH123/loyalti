<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Support\Facades\Auth;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\Support\Browse\Browse;

class SearchInPageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/page/search/:id')
            ->acceptPageParams(['q', 'view', 'when', 'related_comment_friend_only', 'returnUrl'])
            ->navigationConfirmation()
            ->setValue([
                'view' => Browse::VIEW_ALL,
                'when' => Browse::VIEW_ALL,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('core::phrase.search'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['category_id', 'q', 'view']),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->options($this->getWhenOptions()),
        );

        if (app_active('metafox/friend') && Auth::id()) {
            $basic->addField(
                Builder::switch('related_comment_friend_only')
                    ->label(__p('search::phrase.show_results_from_friend'))
                    ->labelPlacement('start')
                    ->fullWidth(),
            );
        }

        $sources = $this->getDataSources();

        if (count($sources)) {
            $basic->addField(
                Builder::simpleCategory('view')
                    ->label(__p('search::phrase.types'))
                    ->defaultValue(Browse::VIEW_ALL)
                    ->dataSource($this->getDataSources())
            );
        }
    }

    protected function getWhenOptions(): array
    {
        return [
            ['label' => __p('core::phrase.when.all'), 'value' => Browse::WHEN_ALL],
            ['label' => __p('core::phrase.when.this_month'), 'value' => Browse::WHEN_THIS_MONTH],
            ['label' => __p('core::phrase.when.this_week'), 'value' => Browse::WHEN_THIS_WEEK],
            ['label' => __p('core::phrase.when.today'), 'value' => Browse::WHEN_TODAY],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function getDataSources(): array
    {
        $collection = resolve(MenuItemRepositoryInterface::class)->getMenuItemByMenuName(
            'group.searchWebCategoryMenu',
            'web',
            true
        );

        return $collection->map(function ($item) {
            return [
                'id'            => $item->name,
                'resource_name' => 'search_type',
                'name'          => __p($item->label),
            ];
        })->toArray();
    }
}
