<?php

/**
 * stub: packages/config/config.stub.
 */

return [
    'shareAssets' => [
        'images/403.png'                  => 'image_error_403',
        'images/404.png'                  => 'image_error_404',
        'images/logo.png'                 => 'image_logo',
        'images/logo-dark.png'            => 'image_logo_dark',
        'images/no-results.png'           => 'image_no_results',
        'images/preview.png'              => 'image_preview',
        'images/sign-in-multi-access.png' => 'image_sign_in_multi_access',
        'images/welcome-image.png'        => 'image_welcome',
        'images/favicon.ico'              => [
            'name'        => 'site_favicon',
            'attach_file' => false,
        ],
        'images/safari-pinned-tab.svg'    => [
            'name' => 'site_mask_icon',
            'attach_file' => false,
        ],
        'images/apple-touch-icon.png'     => [
            'name'        => 'site_apple_touch_icon',
            'attach_file' => false,
        ],
    ],
    'themes' => [
        [
            'theme_id'      => 'a0',
            'title'         => 'Default',
            'resolution'    => 'web',
            'package_id'    => 'metafox/core',
            'total_variant' => 6,
            'is_system'     => 1,
            'is_active'     => 1,
            'created_at'    => now(),
        ],
        [
            'theme_id'      => 'admincp',
            'title'         => 'AdminCP',
            'resolution'    => 'admin',
            'total_variant' => 1,
            'package_id'    => 'metafox/core',
            'is_system'     => 1,
            'is_active'     => 1,
            'created_at'    => now(),
        ],
    ],
    'styles' => [
        [
            'theme_id'   => 'a0',
            'variant_id' => 'a0',
            'title'      => 'Blue',
            'package_id' => 'metafox/core',
            'is_system'  => 1,
            'is_active'  => 1,
            'created_at' => now(),
        ],
        [
            'theme_id'   => 'a0',
            'variant_id' => 'a1',
            'title'      => 'Purple',
            'package_id' => 'metafox/core',
            'is_system'  => 1,
            'is_active'  => 1,
            'created_at' => now(),
        ],
        [
            'theme_id'   => 'a0',
            'variant_id' => 'a2',
            'title'      => 'Red',
            'package_id' => 'metafox/core',
            'is_system'  => 1,
            'is_active'  => 1,
            'created_at' => now(),
        ],
        [
            'theme_id'   => 'a0',
            'variant_id' => 'a3',
            'title'      => 'Green',
            'package_id' => 'metafox/core',
            'is_system'  => 1,
            'is_active'  => 1,
            'created_at' => now(),
        ],
        [
            'theme_id'   => 'a0',
            'variant_id' => 'a4',
            'title'      => 'Deep Blue',
            'package_id' => 'metafox/core',
            'is_system'  => 1,
            'is_active'  => 1,
            'created_at' => now(),
        ],
        [
            'theme_id'   => 'a0',
            'variant_id' => 'a5',
            'title'      => 'Deep Purple',
            'package_id' => 'metafox/core',
            'is_system'  => 1,
            'is_active'  => 1,
            'created_at' => now(),
        ],
        [
            'theme_id'   => 'admincp',
            'variant_id' => 'admincp',
            'title'      => 'AdminCP',
            'package_id' => 'metafox/core',
            'is_system'  => 1,
            'is_active'  => 1,
            'created_at' => now(),
        ],
    ],
];
