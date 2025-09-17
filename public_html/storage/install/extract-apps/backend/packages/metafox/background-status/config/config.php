<?php

$collectionPath = 'public/assets/metafox/background-status/images';
$collectionValuePath = 'assets/metafox/background-status/images';

return [
    'name'            => 'BackgroundStatus',
    'bgs_collections' => [
        [
            'title'           => 'Default status theme',
            'storage_path'    => storage_path("app/{$collectionPath}/o1"),
            'background_path' => "{$collectionValuePath}/o1",
        ],
    ],
];
