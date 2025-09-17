<?php

namespace MetaFox\Cache\Http\Resources\v1\Admin;

use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

class SelectCacheDriverForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('cache::phrase.select_cache_driver'))
            ->action(apiUrl('admin.cache.store.store'))
            ->asPost();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->label(__p('cache::phrase.unique_cache_id_label'))
                    ->description(__p('cache::phrase.unique_cache_id_desc'))
                    ->required()
                    ->yup(Yup::string()->required()->matches('\w+')),
                Builder::choice('driver')
                    ->label(__p('cache::phrase.driver'))
                    ->description(__p('cache::phrase.select_cache_driver_desc'))
                    ->options($this->getDriverOptions())
                    ->yup(Yup::string()->required()),
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.continue'))
            );
    }

    private function getDriverOptions(): array
    {
        $names = resolve(DriverRepositoryInterface::class)->getNamesHasHandlerClass('form-cache');

        $options = [];
        foreach ($names as $id) {
            $options[] = ['label' => $id, 'value' => $id];
        }

        return $options;
    }
}
