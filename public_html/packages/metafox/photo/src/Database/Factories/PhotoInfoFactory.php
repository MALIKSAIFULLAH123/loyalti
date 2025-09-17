<?php

namespace MetaFox\Photo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Photo\Models\PhotoInfo;
use MetaFox\Platform\Support\Factory\HasSetState;

class PhotoInfoFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PhotoInfo::class;

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
