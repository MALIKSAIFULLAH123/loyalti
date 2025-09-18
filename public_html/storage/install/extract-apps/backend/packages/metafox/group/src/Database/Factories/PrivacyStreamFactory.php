<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Group\Models\PrivacyStream;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class PrivacyStreamFactory.
 */
class PrivacyStreamFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PrivacyStream::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}

// end
