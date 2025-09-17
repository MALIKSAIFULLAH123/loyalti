<?php

namespace MetaFox\BackgroundStatus\Support;

use Illuminate\Support\Arr;

class Support
{
    public const BLACK_COLOR = '#000000';
    public const WHITE_COLOR = '#FFFFFF';

    public static function getAllowColor(): array
    {
        return Arr::pluck(self::getColorOptions(), 'value');
    }

    public static function getColorOptions(): array
    {
        return [
            [
                'label' => __p('backgroundstatus::phrase.white_color'),
                'value' => self::WHITE_COLOR,
            ],
            [
                'label' => __p('backgroundstatus::phrase.black_color'),
                'value' => self::BLACK_COLOR,
            ],
        ];
    }
}
