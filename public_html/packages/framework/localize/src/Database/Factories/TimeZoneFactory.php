<?php

namespace MetaFox\Localize\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Models\Timezone;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class TimeZoneFactory.
 * @method Timezone create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class TimeZoneFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Timezone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => 'test/Timezone',
            'offset'        => '+00:00',
            'diff_from_gtm' => 'GMT +00:00',
            'is_active'     => '1',
        ];
    }
}

// end
