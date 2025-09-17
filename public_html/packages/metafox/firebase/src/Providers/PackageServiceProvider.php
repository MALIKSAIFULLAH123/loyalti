<?php

namespace MetaFox\Firebase\Providers;

use MetaFox\Firebase\Support\Firestore;
use MetaFox\Notification\Channels\MobilepushChannel;
use MetaFox\Firebase\Channels\FirebaseMobileChannel;
use MetaFox\Firebase\Channels\FirebaseWebChannel;
use MetaFox\Firebase\Support\CloudMessaging;
use Illuminate\Support\ServiceProvider;
use MetaFox\Notification\Channels\WebpushChannel;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub.
 */

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    /**
     * @var array<string,string>
     */
    public array $singletons = [
        'firebase.fcm'       => CloudMessaging::class,
        'firebase.firestore' => Firestore::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->instance(MobilepushChannel::class, resolve(FirebaseMobileChannel::class));
        $this->app->instance(WebpushChannel::class, resolve(FirebaseWebChannel::class));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Boot facades.
    }
}
