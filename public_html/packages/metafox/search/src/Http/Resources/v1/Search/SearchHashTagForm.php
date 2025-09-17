<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Search\Http\Resources\v1\Search;

use Illuminate\Support\Facades\Auth;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Support\Browse\Browse;

class SearchHashTagForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action(url_utility()->makeApiUrl('hashtag/search'))
            ->asGet()
            ->acceptPageParams(['q', 'from', 'related_comment_friend_only', 'view', 'returnUrl', 'is_hashtag'])
            ->navigationConfirmation()
            ->setValue([
                'is_hashtag'                  => 1,
                'from'                        => Browse::VIEW_ALL,
                'view'                        => Browse::VIEW_ALL,
                'related_comment_friend_only' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $sources = $this->getSourceOptions();

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::hidden('is_hashtag'),
            Builder::searchBox('q')
                ->className('mb2'),
            Builder::dropdown('from')
                ->label(__p('core::web.from'))
                ->marginNormal()
                ->options($this->getOwnerOptions()),
        );

        if (app_active('metafox/friend') && Auth::id()) {
            $basic->addField(
                Builder::switch('related_comment_friend_only')
                    ->label(__p('search::phrase.show_results_from_friend'))
                    ->labelPlacement('start')
                    ->fullWidth()
            );
        }

        if (count($sources)) {
            $basic->addField(
                Builder::simpleCategory('view')
                    ->label(__p('search::phrase.types'))
                    ->defaultValue(Browse::VIEW_ALL)
                    ->dataSource($sources)
            );
        }
    }

    protected function getOwnerOptions(): array
    {
        $options = [
            ['label' => __p('core::phrase.all'), 'value' => Browse::VIEW_ALL],
        ];

        $extraOptions = app('events')->dispatch('search.owner_options');

        if (is_array($extraOptions) && count($extraOptions)) {
            $extraOptions = array_filter($extraOptions, function ($value) {
                return is_array($value);
            });

            $options = array_merge($options, $extraOptions);
        }

        return $options;
    }

    protected function getSourceOptions(): array
    {
        $collection = resolve(MenuItemRepositoryInterface::class)->getMenuItemByMenuName('search.webHashtagCategoryMenu', 'web', true);

        return $collection->map(function ($item) {
            return [
                'id'            => $item->name,
                'resource_name' => 'search_type',
                'name'          => __p($item->label),
            ];
        })->toArray();
    }
}
