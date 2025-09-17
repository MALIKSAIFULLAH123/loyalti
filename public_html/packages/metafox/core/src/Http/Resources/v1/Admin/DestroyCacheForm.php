<?php

namespace MetaFox\Core\Http\Resources\v1\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * Destroy Cache.
 */
class DestroyCacheForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asDelete()
            ->title(__p('core::phrase.clear_cache'))
            ->action('/admincp/cache')
            ->secondAction('@bootstrap')
            ->setValue([
                'data'   => true,
                'config' => true,
                'optimize' => true,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::checkbox('data')
                    ->disabled(true)
                    ->label(__p('core::phrase.clear_data_cache')),
//                Builder::checkbox('config')
//                    ->label(__p('core::phrase.clear_config_cache')),
                Builder::checkbox('optimize')
                    ->label(__p('core::phrase.code_optimize_clear_guide'))
            );
        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.clear_cache'))
            );
    }
}
