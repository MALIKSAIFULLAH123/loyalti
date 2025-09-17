<?php

namespace MetaFox\Cache\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use MetaFox\Form\AbstractForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @driverType form-cache
 * @driverName file
 */
class UpdateFileCacheForm extends Form
{
    use TraitValidateCacheConfiguration;

    protected function prepare(): void
    {
        $resource = $this->resource ?? [];
        $value    = $resource['value'] ?? [];
        $action   = apiUrl('admin.cache.store.update', ['driver' => 'file', 'name' => $resource['name']]);

        $this->title(__p('cache::phrase.edit_cache_store'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('path')
                ->required()
                ->label(__p('cache::phrase.file_path_label'))
                ->description(__p('cache::phrase.file_path_desc'))
                ->yup(Yup::string()->required()->nullable())
        );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request                  $request
     * @return array
     * @throws InvalidArgumentException
     */
    public function validated(Request $request): array
    {
        $config = $request->validate([
            'path' => 'string|required',
        ]);

        $config['driver'] = 'file';

        $this->validateCacheConfiguration($config);

        return $config;
    }
}
