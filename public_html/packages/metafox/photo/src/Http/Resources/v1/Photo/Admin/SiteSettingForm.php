<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Photo\Models\Photo as Model;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class PhotoSiteSettingForm.
 * @property ?Model $resource
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @driverName photo
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module   = 'photo';
        $excludes = [];

        $vars = [
            'photo.allow_photo_category_selection',
            'photo.display_profile_photo_within_gallery',
            'photo.display_cover_photo_within_gallery',
            'photo.display_timeline_photo_within_gallery',
            'photo.photo_allow_uploading_video_to_photo_album',
            'photo.album.minimum_name_length',
            'photo.album.maximum_name_length',
            'photo.default_category',
            // 'photo.allow_uploading_with_video', @deprecated
        ];

        if (!app_active('metafox/video')) {
            $excludes = array_merge($excludes, [
                'photo.photo_allow_uploading_video_to_photo_album',
                // 'photo.allow_uploading_with_video', @deprecated
            ]);
        }

        $vars = Arr::except($vars, $excludes);

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic             = $this->addBasic();
        $maximumNameLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
        $categories        = $this->getCategoryRepository()->getCategoriesForForm();
        $basic->addFields(
            Builder::switch('photo.allow_photo_category_selection')
                ->label(__p('photo::phrase.allow_photo_category_selection'))
                ->description(__p('photo::phrase.allow_photo_category_selection_description')),
            Builder::switch('photo.display_profile_photo_within_gallery')
                ->label(__p('photo::phrase.display_profile_photo_within_gallery'))
                ->description(__p('photo::phrase.display_profile_photo_within_gallery_description')),
            Builder::switch('photo.display_cover_photo_within_gallery')
                ->label(__p('photo::phrase.display_cover_photo_within_gallery'))
                ->description(__p('photo::phrase.display_cover_photo_within_gallery_description')),
            Builder::switch('photo.display_timeline_photo_within_gallery')
                ->label(__p('photo::phrase.display_timeline_photo_within_gallery'))
                ->description(__p('photo::phrase.display_timeline_photo_within_gallery_description')),
            Builder::text('photo.album.minimum_name_length')
                ->required()
                ->label(__p('photo::phrase.album_minimum_name_length'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('photo.album.maximum_name_length')
                ->required()
                ->label(__p('photo::phrase.album_maximum_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(Yup::number()->min(['ref' => 'minimum_name_length']))
                        )
                        ->max($maximumNameLength)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
        );

        if (app_active('metafox/video')) {
            $basic->addFields(
                Builder::switch('photo.photo_allow_uploading_video_to_photo_album')
                    ->label(__p('photo::phrase.photo_allow_uploading_video_to_photo_album'))
                    ->description(__p('photo::phrase.photo_allow_uploading_video_to_photo_album_description')),
            );
        }

        $basic->addFields(
            Builder::choice('photo.default_category')
                ->label(__p('photo::phrase.photo_default_category'))
                ->description(__p('photo::phrase.photo_default_category_description'))
                ->disableClearable()
                ->required()
                ->options($categories)
        );

        $this->addDefaultFooter(true);
    }

    protected function getCategoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function validated(Request $request): array
    {
        $data = $request->all();

        $excludes = [];

        if (!app_active('metafox/video')) {
            $excludes = array_merge($excludes, [
                'photo.photo_allow_uploading_video_to_photo_album',
            ]);
        }

        return Arr::except($data, $excludes);
    }
}
