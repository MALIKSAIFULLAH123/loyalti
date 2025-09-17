<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use Illuminate\Support\Facades\Auth;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\Support\Browse\Browse;

class GlobalSearchHashtagMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action(apiUrl('search.index'))
            ->asGet()
            ->acceptPageParams(['q', 'from', 'related_comment_friend_only', 'view', 'is_hashtag'])
            ->setValue([
                'is_hashtag'                  => 1,
                'from'                        => Browse::VIEW_ALL,
                'view'                        => Browse::VIEW_ALL,
                'related_comment_friend_only' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->showWhen(['falsy', 'filters']);
        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->placeholder(__p('core::phrase.search')),
            Builder::choice('from')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::web.from'))
                ->marginNormal()
                ->options($this->getOwnerOptions()),
        );

        if (app_active('metafox/friend') && Auth::id()) {
            $basic->addField(
                Builder::switch('related_comment_friend_only')
                    ->forBottomSheetForm()
                    ->label(__p('search::phrase.show_results_from_friend'))
                    ->labelPlacement('start')
                    ->fullWidth()
            );
        }

        $bottomSheet = $this->addSection(['name' => 'bottomSheet']);
        $bottomSheet->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen(['truthy', 'filters'])
                ->targets(['from', 'related_comment_friend_only']),
            Builder::choice('from')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('core::web.from'))
                ->options($this->getOwnerOptions())
                ->showWhen(['truthy', 'filters'])
        );

        if (app_active('metafox/friend') && Auth::id()) {
            $bottomSheet->addField(
                Builder::switch('related_comment_friend_only')
                    ->forBottomSheetForm()
                    ->variant('standard-inlined')
                    ->label(__p('search::phrase.show_results_from_friend'))
                    ->showWhen(['truthy', 'filters'])
            );
        }

        $bottomSheet->addField(
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
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
