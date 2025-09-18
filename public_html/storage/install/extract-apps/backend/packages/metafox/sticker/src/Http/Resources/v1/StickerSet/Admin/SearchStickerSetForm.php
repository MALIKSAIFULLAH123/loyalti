<?php

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Sticker\Models\StickerSet as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchStickerSetForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchStickerSetForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('sticker::phrase.new_sticker_set'))
            ->action(apiUrl('admin.sticker.sticker-set.index'))
            ->acceptPageParams(['q']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();
        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm(),
            Builder::submit()
                ->forAdminSearchForm(),
        );
    }
}
