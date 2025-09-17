<?php

namespace MetaFox\SEO\Http\Resources\v1\Admin;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\Driver;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    /**
     * @var Collection<Driver>
     */
    private Collection $entities;

    public function __construct($resource = null)
    {
        parent::__construct($resource);
        $this->entities = $this->initData();
    }

    protected function prepare(): void
    {
        $module       = 'seo';
        $excludeTypes = Settings::get('seo.sitemap_exclude_types', []);
        $includeTypes = collect($this->entities)
            ->filter(function (Driver $driver) use ($excludeTypes) {
                return !in_array($driver->name, $excludeTypes, true);
            })->pluck('name')->toArray();

        $value = [
            'seo.sitemap_include_types' => $includeTypes,
        ];

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue(Arr::undot($value));
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::checkboxGroup('seo.sitemap_include_types')
                ->options($this->getOptions())
                ->label(__p('seo::phrase.enabling_sitemap'))
                ->description(__p('seo::phrase.enabling_sitemap_desc'))
                ->enableCheckAll(),
        );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function validated(Request $request): array
    {
        $data         = $request->all();
        $includeTypes = Arr::get($data, 'seo.sitemap_include_types', []);
        $excludeTypes = collect($this->entities)->filter(function (Driver $driver) use ($includeTypes) {
            return !in_array($driver->name, $includeTypes, true);
        })->pluck('name')->toArray();

        Arr::set($data, 'seo.sitemap_exclude_types', $excludeTypes);
        Arr::forget($data, 'seo.sitemap_include_types');

        return $data;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getOptions(): array
    {
        return collect($this->entities)
            ->map(function (Driver $driver) {
                return [
                    'label' => $driver->title,
                    'value' => $driver->name,
                ];
            })
            ->sortBy(function (array $entity) {
                return $entity['value'];
            })
            ->values()
            ->toArray();
    }

    /**
     * @return Collection<Driver>
     */
    protected function initData(): Collection
    {
        $configs = PackageManager::discoverSettings('getSitemap');

        $types = Arr::collapse(array_values($configs));

        return localCacheStore()->rememberForever(__CLASS__ . 'sitemap_entities_data', function () use ($types) {
            $driverRepository = resolve(DriverRepositoryInterface::class);
            $drivers          = $driverRepository
                ->getModel()
                ->newModelQuery()
                ->where('type', '=', Constants::DRIVER_TYPE_ENTITY)
                ->whereIn('name', $types)
                ->get();

            return collect($drivers)
                ->filter(function (Driver $driver) {
                    $modelClass = Relation::getMorphedModel($driver->name);

                    if (!$modelClass || !class_exists($modelClass)) {
                        return false;
                    }

                    $model = resolve($modelClass);
                    if (!$model instanceof Entity) {
                        return false;
                    }

                    return true;
                })
                ->values();
        });
    }
}
