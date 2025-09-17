<?php

namespace MetaFox\Layout\Http\Resources\v1\Build\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

class CreateBuild extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('core::phrase::create_site_bundle'))
            ->noHeader(false)
            ->action(apiUrl('admin.layout.build.store'));
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::typography('note1')
                    ->plainText(__p('layout::phrase.rebuite_site_guide'))
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('layout::phrase.rebuild_site')),
                Builder::cancelButton()
            );
    }
}
