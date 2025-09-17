<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\Admin;

use MetaFox\BackgroundStatus\Models\BgsCollection as Model;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreCollectionForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreCollectionForm extends AbstractForm
{
    protected const MAX_LENGTH_TITLE = 100;

    protected const MIN_LENGTH_TITLE = 3;
    public const    PHOTO_MINE_TYPES = ['image/jpg', 'image/jpeg', 'image/png'];

    public function boot(BgsCollectionRepositoryInterface $repository, ?int $id = null)
    {
    }

    protected function prepare(): void
    {
        $this->action(apiUrl('admin.bgs.collection.store'))
            ->asPost()
            ->setValue([
                'is_active' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::translatableText('title')
                    ->required()
                    ->maxLength(self::MAX_LENGTH_TITLE)
                    ->minLength(self::MIN_LENGTH_TITLE)
                    ->label(__p('backgroundstatus::phrase.collection_name'))
                    ->yup(
                        Yup::string()
                            ->required()
                            ->minLength(self::MIN_LENGTH_TITLE)
                            ->maxLength(self::MAX_LENGTH_TITLE)
                    )
                    ->buildFields(),
                Builder::uploadMultiMedia('background_temp_file')
                    ->required()
                    ->allowEditPhoto()
                    ->editPhotoAction([
                        'module_name'   => 'backgroundstatus',
                        'resource_name' => 'pstatusbg_background',
                        'action_name'   => 'editBackgroundForm',
                    ])
                    ->mappingEditPhotoFields(['text_color'])
                    ->label(__p('backgroundstatus::phrase.add_photos'))
                    ->accepts(implode(',', self::PHOTO_MINE_TYPES))
                    ->acceptFail(__p('backgroundstatus::phrase.photo_accept_type_fail'))
                    ->itemType('backgroundstatus')
                    ->uploadUrl('file')
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
                Builder::checkbox('is_active')
                    ->label(__p('core::phrase.is_active')),
            );

        $this->addDefaultFooter();
    }
}
