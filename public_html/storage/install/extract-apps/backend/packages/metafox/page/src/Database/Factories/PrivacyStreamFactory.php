<?php

namespace MetaFox\Page\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Page\Models\PrivacyStream;
use MetaFox\Platform\Support\Factory\HasSetState;

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
