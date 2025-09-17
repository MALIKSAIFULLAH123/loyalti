<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\SortScope;
use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\ViewScope;
use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\WhenScope;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use Foxexpert\Sevent\Models\Sevent as Model;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Platform\Facades\Settings;

/**
 * @preload 1
 */
class SearchSeventMapForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/sevent/search-map')
            ->acceptPageParams([
                'q', 'sview','distance', 'sort', 'limit', 'returnUrl', 'bounds_west',
                'bounds_east', 'bounds_south', 'bounds_north', 'zoom', 'view',
                'q', 'sort',
            'sview','distance', 'when', 'country_iso',  'category_id', 'returnUrl', 'is_featured'
            ])
            ->setValue(['view' => ViewScope::VIEW_ON_MAP]);
    }

    protected function initialize(): void
    {
        $context = user();
        $categories = resolve(CategoryRepositoryInterface::class)->viewForAdmin($context, []);
        $categoryOptions = [];
        foreach ($categories as $category) {
            $categoryOptions[] = [
                'label' => __p($category->name), 
                'value' => $category->id
            ];
        }

        $basic = $this->addBasic()
            ->sxContainer(['alignItems' => 'center']);
            
        $countryField = $this->getCountryField();

        $basic->addFields(
            Builder::searchTextBox('q')
            ->forAdminSearchForm()
            ->sizeLarge()
            ->marginNormal()
            ->placeholder(__p('sevent::phrase.search_sevents'))
            ->sxFieldWrapper($this->getResponsiveTextSx()),
        Builder::choice('distance')
            ->label(__p('sevent::phrase.distance'))
            ->marginNormal()
            ->sxFieldWrapper($this->getResponsiveSortSx())
            ->options($this->getLimitOptions()),
        $countryField,
        Builder::choice('category_id')
            ->label(__p('core::phrase.category'))
            ->sxFieldWrapper($this->getResponsiveCategorySx())
            ->marginNormal()
            ->options($categoryOptions)
            ->sizeLarge(),
        Builder::choice('sview')
            ->label(__p('sevent::phrase.price_view'))
            ->sxFieldWrapper($this->getResponsiveSortSx())
            ->marginNormal()
            ->sizeLarge()
            ->options([
                ['label' => __p('sevent::phrase.price_free'), 'value' => 'free'],
                ['label' => __p('sevent::phrase.price_paid'), 'value' => 'paid'],
                [
                    'label' => __p('sevent::phrase.upcoming'),
                    'value' => WhenScope::WHEN_UPCOMING,
                ], 
                [
                    'label' => __p('sevent::phrase.ongoing'),
                    'value' => WhenScope::WHEN_ONGOING,
                ],
                [
                    'label' => __p('sevent::phrase.past'),
                    'value' => WhenScope::WHEN_PAST,
                ],
            ]),
        Builder::choice('sort')
            ->label(__p('core::phrase.sort_label'))
            ->sxFieldWrapper($this->getResponsiveSortSx())
            ->marginNormal()
            ->sizeLarge()
            ->options([
                ['label' => __p('core::phrase.sort.recent'), 'value' => Browse::SORT_LATEST],
                ['label' => __p('sevent::phrase.popular'), 'value' => 'popular'],
                ['label' => __p('sevent::phrase.start_soon'), 'value' => 'start_soon'],
                ['label' => __p('core::phrase.sort.most_viewed'), 'value' => Browse::SORT_MOST_VIEWED], 
                ['label' => __p('core::phrase.sort.most_liked'), 'value' => Browse::SORT_MOST_LIKED], 
                ['label' => __p('core::phrase.sort.most_discussed'), 'value' => Browse::SORT_MOST_DISCUSSED]]),
        );
    }

    protected function getLimitOptions(): array
    {
        return [
            [
                'label' => __p('sevent::phrase.50_miles'),
                'value' => 50,
            ], [
                'label' => __p('sevent::phrase.100_miles'),
                'value' => 100,
            ], [
                'label' => __p('sevent::phrase.200_miles'),
                'value' => 200,
            ], [
                'label' => __p('sevent::phrase.300_miles'),
                'value' => 300,
            ],
        ];
    }

    protected function getCountryField()
    {
        return  Builder::choice('country_iso')
            ->label(__p('core::country.country'))
            ->marginNormal()
            ->sxFieldWrapper($this->getResponsiveSortSx())
            ->options(Country::buildCountrySearchForm());
    }

    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '100%',
                'md' => '100%',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '100%',
                'md' => '100%',
            ],
        ];
    }

    protected function getResponsiveSortSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '100%',
                'md' => '100%',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '100%',
                'md' => '100%',
            ],
        ];
    }

    protected function getResponsiveCategorySx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '100%',
                'md' => '100%',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '100%',
                'md' => '100%',
            ],
        ];
    }

    protected function getResponsiveTextSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '100%',
                'md' => '100%',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '100%',
                'md' => '100%',
            ],
        ];
    }
}
