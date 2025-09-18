<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'marketplace';

        $vars = [
            'maximum_title_length',
            'minimum_title_length',
            'days_to_expire',
            'days_to_notify_before_expire',
            'default_category',
            'enable_short_description_field',
            'commission_fee'
        ];

        $value = [];

        foreach ($vars as $var) {
            $var = $module . '.' . $var;

            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $categories = resolve(CategoryRepositoryInterface::class)->getCategoriesForForm();

        $basic->addFields(
            Builder::text('marketplace.minimum_title_length')
                ->label(__p('marketplace::phrase.minimum_title_length'))
                ->required()
                ->yup(
                    Yup::number()
                        ->required(__p('marketplace::phrase.minimum_title_length_description_required', [
                            'max' => MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH,
                        ]))
                        ->unint()
                        ->min(MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('marketplace.maximum_title_length')
                ->label(__p('marketplace::phrase.maximum_title_length'))
                ->required()
                ->yup(
                    Yup::number()
                        ->required(__p('marketplace::phrase.maximum_title_length_description_required', [
                            'max' => MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH,
                        ]))
                        ->unint()
                        ->when(
                            Yup::when('minimum_title_length')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->min(['ref' => 'minimum_title_length'])
                                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                        ->max(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('marketplace.days_to_expire')
                ->required()
                ->label(__p('marketplace::phrase.days_to_expire_label'))
                ->description(__p('marketplace::phrase.days_to_expire_desc'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                        ->unint()
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('marketplace.days_to_notify_before_expire')
                ->label(__p('marketplace::phrase.days_to_notify_before_expire_label'))
                ->description(__p('marketplace::phrase.days_to_notify_before_expire_desc'))
                ->required()
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                        ->unint()
                        ->when(
                            Yup::when('days_to_expire')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->lessThan(['ref' => 'days_to_expire'])
                                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::choice('marketplace.default_category')
                ->label(__p('marketplace::phrase.listing_default_category'))
                ->description(__p('marketplace::phrase.listing_default_category_description'))
                ->required()
                ->options($categories)
                ->yup(
                    Yup::number()
                        ->min(1)
                ),
            Builder::switch('marketplace.enable_short_description_field')
                ->label(__p('marketplace::phrase.enable_short_description_field'))
                ->description(__p('marketplace::phrase.enable_short_description_field_desc')),
            Builder::text('marketplace.commission_fee')
                ->required()
                ->label(__p('marketplace::phrase.commission_fee_setting_label'))
                ->description(__p('marketplace::phrase.commission_fee_setting_description'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                )
        );

        $this->addDefaultFooter(true);
    }
}
