<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Story\Models\BackgroundSet as Model;
use MetaFox\Story\Repositories\BackgroundSetRepositoryInterface;
use MetaFox\Story\Support\StorySupport;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreBackgroundSetForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreBackgroundSetForm extends AbstractForm
{
    protected const MAX_LENGTH_TITLE = 100;

    protected const MIN_LENGTH_TITLE = 3;
    public const    PHOTO_MINE_TYPES = ['image/jpg', 'image/jpeg', 'image/png'];

    public function boot(BackgroundSetRepositoryInterface $repository, ?int $id = null)
    {
    }

    protected function prepare(): void
    {
        $this->action(apiUrl('admin.story.background-set.store'))
            ->asPost()
            ->setValue([
                'is_active' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('title')
                    ->required()
                    ->maxLength(self::MAX_LENGTH_TITLE)
                    ->minLength(self::MIN_LENGTH_TITLE)
                    ->label(__p('story::phrase.collection_name'))
                    ->yup(
                        Yup::string()
                            ->required()
                            ->minLength(self::MIN_LENGTH_TITLE)
                            ->maxLength(self::MAX_LENGTH_TITLE)
                    ),
                Builder::uploadMultiMedia('background_temp_file')
                    ->required()
                    ->label(__p('core::phrase.add_photos'))
                    ->accepts(implode(',', self::PHOTO_MINE_TYPES))
                    ->acceptFail(__p('story::phrase.photo_accept_type_fail'))
                    ->itemType('story')
                    ->uploadUrl('file')
                    ->setAttributes([
                        'aspectRatio'   => 916,
                        'columnSpacing' => 1.5,
                        'rowSpacing'    => 1.5,
                        'grid'          => [
                            'md' => 2.4,
                            'sm' > 4,
                            'xs' => 6,
                        ],
                    ])
                    ->thumbnailSizes(StorySupport::RESIZE_IMAGE)
                    ->allowDrop()
                    ->yup(Yup::array()
                        ->required()
                        ->minWhen([
                            'value' => 1,
                            'when'  => [
                                'includes', 'item.status',
                                [MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS],
                            ],
                        ], __p('validation.this_field_is_a_required_field'))
                        ->of(
                            Yup::object()
                                ->addProperty('id', Yup::number())
                                ->addProperty('type', Yup::string())
                                ->addProperty('status', Yup::string())
                        )),
            );

        $this->addDefaultFooter();
    }
}
