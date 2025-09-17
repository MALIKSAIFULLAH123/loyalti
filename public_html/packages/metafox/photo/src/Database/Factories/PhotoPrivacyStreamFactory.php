<?php

namespace MetaFox\Photo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Photo\Models\PhotoPrivacyStream;
use MetaFox\Platform\Support\Factory\HasSetState;

class PhotoPrivacyStreamFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PhotoPrivacyStream::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
        ];
    }
}

// end
