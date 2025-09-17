<?php

namespace MetaFox\Event\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
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
 * Class EventSiteSettingForm.
 * @property ?Model $resource
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'event';
        $vars   = [
            'event.minimum_name_length',
            'event.maximum_name_length',
            'event.default_category',
            'event.number_hours_expiration_invite_code',
            'event.default_time_format',
            'event.invite_expiration_role',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $maximumNameLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
        $basic             = $this->addBasic();
        $categories        = $this->getCategoryRepository()->getCategoriesForForm();
        $basic->addFields(
            Builder::text('event.minimum_name_length')
                ->required()
                ->label(__p('event::admin.minimum_name_length'))
                ->description(__p('event::admin.minimum_name_length_desc'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->required(__p('event::validation.minimum_name_length_description_required', ['min' => 1]))
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('event.maximum_name_length')
                ->required()
                ->label(__p('event::admin.maximum_name_length'))
                ->description(__p('event::admin.maximum_name_length_desc'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()
                        ->int()
                        ->required(__p('event::validation.maximum_name_length_description_required'))
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->int()
                                        ->min(['ref' => 'minimum_name_length'])
                                        ->max($maximumNameLength)
                                        ->required(__p('event::validation.maximum_name_length_description_required'))
                                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                        ->max($maximumNameLength)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::choice('event.default_time_format')
                ->label(__p('event::admin.default_time_format_label'))
                ->options([
                    [
                        'label' => __p('event::admin.format_12_hour'),
                        'value' => 12,
                    ], [
                        'label' => __p('event::admin.format_24_hour'),
                        'value' => 24,
                    ],
                ]),
            Builder::text('event.number_hours_expiration_invite_code')
                ->returnKeyType('next')
                ->label(__p('event::admin.number_hours_expiration_invite_code_label'))
                ->description(__p('event::admin.number_hours_expiration_desc'))
                ->yup(Yup::number()
                    ->int()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
            Builder::text('event.invite_expiration_role')
                ->returnKeyType('next')
                ->label(__p('event::admin.number_hours_expiration_invite_role_label'))
                ->description(__p('event::admin.number_hours_expiration_desc'))
                ->yup(Yup::number()
                    ->int()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
            Builder::choice('event.default_category')
                ->label(__p('event::admin.event_default_category'))
                ->description(__p('event::admin.event_default_category_description'))
                ->disableClearable()
                ->required()
                ->options($categories),
        );

        $this->addDefaultFooter(true);
    }

    protected function getCategoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }
}
