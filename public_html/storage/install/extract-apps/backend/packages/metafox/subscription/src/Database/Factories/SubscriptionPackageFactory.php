<?php

namespace MetaFox\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Subscription\Models\SubscriptionPackage;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class SubscriptionPackageFactory.
 * @method SubscriptionPackage create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class SubscriptionPackageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubscriptionPackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'                                         => $this->faker->words(2, true),
            'status'                                        => 'active',
            'price'                                         => '{"USD":10,"EUR":11,"GBP":12}',
            'recurring_price'                               => null,
            'recurring_period'                              => null,
            'upgraded_role_id'                              => 4,
            'image_path'                                    => null,
            'image_server_id'                               => 'public',
            'is_on_registration'                            => false,
            'is_popular'                                    => false,
            'is_free'                                       => false,
            'allowed_renew_type'                            => null,
            'days_notification_before_subscription_expired' => 3,
            'background_color_for_comparison'               => '#ebf1f5',
            'visible_roles'                                 => '',
            'ordering'                                      => 1,
            'total_success'                                 => 0,
            'total_pending'                                 => 0,
            'total_canceled'                                => 0,
            'total_expired'                                 => 0,
        ];
    }

    public function setUser()
    {
        return $this;
    }

    public function setOwner($user)
    {
        return $this;
    }
}

// end
