<?php

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Sticker\Models\StickerSet as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreStickerSetForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreStickerSetForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('sticker::phrase.new_sticker_set'))
            ->action(apiUrl('admin.sticker.sticker-set.store'))
            ->asPost()
            ->setValue([
                'is_active' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('title')
                ->required()
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('core::phrase.fill_in_a_title'))
                ->yup(
                    Yup::string()
                        ->minLength(MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH)
                        ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                        ->required()
                ),
            $this->buildUploadStickerField(),
            Builder::checkbox('is_active')
                ->fullWidth()
                ->marginNormal()
                ->label(__p('core::phrase.is_active')),
        );

        $this->addDefaultFooter($this->isEdit());
    }

    protected function buildUploadStickerField(): AbstractField
    {
        $field = Builder::uploadMultiMedia('file')
            ->required()
            ->label(__p('sticker::phrase.add_stickers'))
            ->placeholder(__p('sticker::phrase.add_stickers'))
            ->description(__p('sticker::phrase.the_sticker_must_be_a_gif_file'))
            ->accepts('.gif')
            ->allowDrop()
            ->itemType('sticker')
            ->uploadUrl('file');

        $yupObjectSticker = Yup::object()
            ->addProperty('id', Yup::number())
            ->addProperty('type', Yup::string())
            ->addProperty('status', Yup::string());

        $yup = Yup::array()
            ->minWhen([
                'value' => 1,
                'when'  => [
                    'includes', 'item.status',
                    [MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS],
                ],
            ], __p('validation.this_field_is_a_required_field'))
            ->of($yupObjectSticker);

        $field->yup($yup);

        return $field;
    }

    protected function isEdit(): bool
    {
        return false;
    }
}
