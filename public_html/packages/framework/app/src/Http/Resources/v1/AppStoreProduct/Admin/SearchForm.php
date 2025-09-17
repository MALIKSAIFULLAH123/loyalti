<?php

namespace MetaFox\App\Http\Resources\v1\AppStoreProduct\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchForm.
 * @property array<string, mixed> $resource
 */
class SearchForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader(true)
            ->asGet()
            ->title(__p('core::phrase.search'))
            ->action('/app/store/product')
            ->acceptPageParams(['q', 'type', 'category', 'price_filter', 'sort', 'featured'])
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'type'         => 'app',
                'category'     => 'all',
                'price_filter' => 'all',
                'sort'         => 'latest',
                'featured'     => 'all',
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['variant' => 'horizontal']);

        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.search_dot')),
            Builder::choice('type')
                ->forAdminSearchForm()
                ->disableClearable()
                ->label(__p('core::phrase.type'))
                ->placeholder(__p('core::phrase.type'))
                ->options($this->getAllowedOptionsForForm('type')),
            Builder::choice('category')
                ->forAdminSearchForm()
                ->disableClearable()
                ->label(__p('core::phrase.category'))
                ->placeholder(__p('core::phrase.all_category'))
                ->options($this->getCategoriesOptions($this->resource)),
            Builder::choice('price_filter')
                ->forAdminSearchForm()
                ->disableClearable()
                ->label(__p('core::phrase.price'))
                ->placeholder(__p('core::phrase.all_price'))
                ->options($this->getAllowedOptionsForForm('price_filter')),
            Builder::choice('sort')
                ->forAdminSearchForm()
                ->disableClearable()
                ->label(__p('core::phrase.sort_label'))
                ->placeholder(__p('core::phrase.sort_by'))
                ->options($this->getAllowedOptionsForForm('sort')),
            Builder::choice('featured')
                ->forAdminSearchForm()
                ->disableClearable()
                ->label(__p('core::phrase.featured'))
                ->placeholder(__p('core::phrase.all'))
                ->options($this->getAllowedOptionsForForm('featured')),
            Builder::submit()
                ->forAdminSearchForm()
                ->label(__p('core::phrase.search')),
        );
    }

    /**
     * getAllowedTypes.
     *
     * @param  string        $key
     * @return array<string>
     */
    public static function getAllowedOptions(string $key): array
    {
        $availableOptions = [
            'type'         => ['all', 'app', 'language', 'theme'],
            'price_filter' => ['all', 'free', 'paid'],
            'sort'         => ['latest', 'recent_updated',  'top_rated'],
            'featured'     => ['all', 'yes', 'no'],
        ];

        return Arr::get($availableOptions, $key, []);
    }

    /**
     * getAllowedTypesForForm.
     *
     * @param  string                     $key
     * @return array<array<string,mixed>>
     */
    public function getAllowedOptionsForForm(string $key): array
    {
        $options = [];

        foreach (self::getAllowedOptions($key) as $option) {
            array_push($options, [
                'label' => $option !== 'all' ? __p("core::store.$key.$option") : __p('core::phrase.all'),
                'value' => $option,
            ]);
        }

        return $options;
    }

    public function getCategoriesOptions(array $data): array
    {
        $rawOptions = Arr::get($data, 'elements.basic.elements.category.options');

        $default = [
            'label' => __p('core::phrase.all'),
            'value' => 'all',
        ];

        $options = collect($rawOptions)
            ->map(function (array $option) {
                return [
                    'label' => htmlspecialchars_decode(Arr::get($option, 'label')),
                    'value' => Arr::get($option, 'value'),
                ];
            })
            ->values()
            ->toArray();

        return [$default, ...$options];
    }
}
