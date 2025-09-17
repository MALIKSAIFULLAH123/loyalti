<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\PolicyGate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<string, string>
     */
    protected $policies = [];

    /**
     * @var array
     */
    protected array $rules = [];

    /**
     * Register any authentication/authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }

    public function register()
    {
        $this->booting(function () {
            $this->discoverPolicies();
        });
    }

    protected function discoverPolicies(): void
    {
        try {
            // Developer to change key suffix etc v5. if data structure is changed
            [$policies, $rules] = localCacheStore()->rememberForever(
                'discoverPackagePolicies.v2',
                function () {
                    $repository = resolve(DriverRepositoryInterface::class);
                    $policies   = $repository->loadDrivers(Constants::DRIVER_TYPE_POLICY_RESOURCE, null, true, null);
                    $rules      = $repository->loadDrivers(Constants::DRIVER_TYPE_POLICY_RULE, null, true, null);

                    return [$policies, $rules];
                }
            );

            foreach ($policies as $policy) {
                $this->policies[$policy[0]] = $policy[1];
                PolicyGate::addPolicy($policy[0], $policy[1]);
            }

            foreach ($rules as $rule) {
                Gate::define($rule[0], "{$rule[1]}@check");
                PolicyGate::addRule($rule[0], $rule[1]);
            }
        } catch (\Throwable $exception) {
            // missing installed value
        }
    }
}
