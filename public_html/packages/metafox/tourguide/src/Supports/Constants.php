<?php

namespace MetaFox\TourGuide\Supports;

class Constants
{
    public const EVERYONE = 0;
    public const MEMBER   = 1;
    public const GUEST    = 2;

    public const START_TOUR_ICON  = 'ico-tourguide';
    public const CREATE_TOUR_ICON = 'ico-plus-circle';
    public const CLOSE_TOUR_ICON  = 'ico-close';

    public const CREATE_TOUR_ACTION_NAME = 'create_tour';
    public const START_TOUR_ACTION_NAME  = 'start_tour';
    public const CLOSE_TOUR_ACTION_NAME  = 'close_tour';

    public const DEFAULT_TOUR_GUIDE_DELAY_TIME      = 5;
    public const DEFAULT_TOUR_GUIDE_BUTTON_POSITION = [
        'position' => [
            'top'   => '10%',
            'right' => '1%',
        ],
    ];
}
