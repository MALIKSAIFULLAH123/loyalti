<?php

namespace MetaFox\LiveStreaming\Support\Handlers;

use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Yup\Yup;

class EditPermissionListener
{
    public static function minLimitLiveTime(string $name, string $label, string $description): FormField
    {
        return Builder::text($name)
            ->asNumber()
            ->preventScrolling()
            ->required()
            ->label(__p($label))
            ->description(__p($description))
            ->yup(
                Yup::number()
                    ->required()
                    ->int()
                    ->notOneOf([1, 2, 3], __p('livestreaming::validation.limit_time_for_each_live_video_must_greater_than_or_equal_to', ['number' => 4]))
                    ->min(0, __p('livestreaming::validation.limit_time_for_each_live_video_must_greater_than_or_equal_to', ['number' => 4]))
            );
    }
}
