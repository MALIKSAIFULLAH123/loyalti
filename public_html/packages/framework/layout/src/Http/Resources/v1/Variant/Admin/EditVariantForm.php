<?php

namespace MetaFox\Layout\Http\Resources\v1\Variant\Admin;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Layout\Models\Variant as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditVariantForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditVariantForm extends AbstractForm
{
    public const    PHOTO_MINE_TYPES = ['image/jpg', 'image/jpeg', 'image/png'];

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('/admincp/layout/variant/' . $this->resource?->id)
            ->asPut()
            ->setValue(
                [
                    'title'     => $this->resource->title,
                    'thumbnail' => [
                        'id' => (int) $this->resource->thumb_id,
                    ],
                ]
            );
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('title')
                    ->required()
                    ->label(__p('core::phrase.name'))
                    ->yup(Yup::string()->required()),
                $this->buildThumbnailField(),
            );

        $this->addDefaultFooter();
    }

    private function buildThumbnailField(): AbstractField
    {
        $field = Builder::singlePhoto('thumbnail')
            ->required()
            ->label(__p('layout::phrase.add_thumbnail'))
            ->accepts(implode(',', self::PHOTO_MINE_TYPES))
            ->acceptFail(__p('layout::phrase.thumbnail_accept_type_fail'))
            ->itemType('variant')
            ->uploadUrl('file')
            ->previewUrl($this->resource?->imageUrl);

        $field->yup(
            Yup::object()
                ->required()
                ->addProperty('id', Yup::number()
                    ->required(__p('layout::phrase.thumbnail_is_a_required_field')))
        );

        return $field;
    }
}
