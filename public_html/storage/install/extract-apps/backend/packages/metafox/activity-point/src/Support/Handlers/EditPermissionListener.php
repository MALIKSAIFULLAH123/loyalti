<?php

namespace MetaFox\ActivityPoint\Support\Handlers;

use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Yup\Yup;

class EditPermissionListener
{
    public static function maximumActivityPointsAdminCanAdjust(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->required()
                    ->int()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
            );
    }

    public static function periodTimeAdminAdjustActivityPoints(string $name, string $label, string $description): FormField
    {
        return Builder::choice($name)
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->options([
                [
                    'label' => __p('activitypoint::phrase.per_day'),
                    'value' => 1,
                ],
                [
                    'label' => __p('activitypoint::phrase.per_week'),
                    'value' => 2,
                ],
                [
                    'label' => __p('activitypoint::phrase.per_month'),
                    'value' => 3,
                ],
                [
                    'label' => __p('activitypoint::phrase.per_year'),
                    'value' => 4,
                ],
            ]);
    }

    public static function minPointsForConversion(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->asNumber()
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->nullable()
                    ->required()
                    ->int()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
            );
    }

    public static function maxPointsForConversion(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->asNumber()
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->nullable()
                    ->required()
                    ->int()
                    ->min(0)
                    ->when(
                        Yup::when('min_points_for_conversion')
                            ->is('$exists')
                            ->then(
                                Yup::number()
                                    ->when(
                                        Yup::when('max_points_for_conversion')
                                            ->is(0)
                                            ->then(
                                                Yup::number()->min(0)
                                            )
                                            ->otherwise(
                                                Yup::number()
                                                    ->min(['ref' => 'min_points_for_conversion'])
                                            )
                                    )
                            )
                    )
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
            );
    }
}
