<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use Illuminate\Support\Facades\Auth;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\Support\Browse\Browse;

class GlobalSearchMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action(apiUrl('search.index'))
            ->asGet()
            ->acceptPageParams(['q', 'view'])
            ->setValue([
                'is_hashtag'                  => 0,
                'from'                        => Browse::VIEW_ALL,
                'view'                        => Browse::VIEW_ALL,
                'related_comment_friend_only' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->showWhen(['falsy', 'filters']);
        $basic->addFields(
            Builder::button('filters')
                ->forBottomSheetForm('SFFilterButton')
                ->showWhen([
                    'and',
                    ['truthy', 'is_hashtag'],
                ]),
            Builder::hidden('is_hashtag'),
        );

        $bottomSheet = $this->addSection(['name' => 'bottomSheet']);
        $bottomSheet->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen([
                    'and',
                    ['truthy', 'filters'],
                    ['truthy', 'is_hashtag'],
                ])
                ->targets(['from', 'related_comment_friend_only', 'hashtag']),
            Builder::choice('from')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('core::web.from'))
                ->options($this->getOwnerOptions())
                ->showWhen([
                    'and',
                    ['truthy', 'filters'],
                    ['truthy', 'is_hashtag'],
                ]),
            Builder::autocomplete('hashtag')
                ->forBottomSheetForm()
                ->useOptionContext()
                ->variant('standard-inlined')
                ->label(__p('search::phrase.trending'))
                ->showWhen([
                    'and',
                    ['truthy', 'filters'],
                    ['truthy', 'is_hashtag'],
                ])
                ->valueKey('text')
                ->searchEndpoint('/search/hashtag/trending')
                ->searchParams(['limit' => 10]),
        );

        if (app_active('metafox/friend') && Auth::id()) {
            $bottomSheet->addField(
                Builder::switch('related_comment_friend_only')
                    ->forBottomSheetForm()
                    ->variant('standard-inlined')
                    ->label(__p('search::phrase.show_results_from_friend'))
                    ->showWhen([
                        'and',
                        ['truthy', 'filters'],
                        ['truthy', 'is_hashtag'],
                    ])
            );
        }

        $bottomSheet->addField(
            Builder::submit()
                ->showWhen([
                    'and',
                    ['truthy', 'filters'],
                    ['truthy', 'is_hashtag'],
                ])
                ->label(__p('core::phrase.show_results'))
        );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getOwnerOptions(): array
    {
        $options = [
            ['label' => __p('core::phrase.all'), 'value' => Browse::VIEW_ALL],
        ];

        $extraOptions = app('events')->dispatch('search.owner_options');

        if (!is_array($extraOptions)) {
            return [];
        }

        $extraOptions = array_filter($extraOptions, function ($value) {
            return is_array($value);
        });

        return array_merge($options, $extraOptions);
    }
}
