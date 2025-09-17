<?php

return [
    'shareAssets'      => [
        'images/no_image.png'         => 'no_image',
        'images/video_processing.png' => 'video_in_processing_image',
    ],
    'default_provider' => env('MFOX_VIDEO_PROVIDER', 'ffmpeg'),
];
