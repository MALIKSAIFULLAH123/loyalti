<?php

namespace MetaFox\Search\Http\Resources\v1\Reindex\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ReindexForm.
 * @ignore
 * @codeCoverageIgnore
 */
class ReindexForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('search::phrase.reindex'))
            ->asPost()
            ->action(apiUrl('admin.search.reindex.store'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::typography('description')
                    ->plainText(__p('search::phrase.reindexing_desc')),
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('search::phrase.reindex')),
            );
    }
}
