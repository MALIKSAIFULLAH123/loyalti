<?php

namespace MetaFox\Marketplace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Marketplace\Models\Image;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class ImageFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class ImageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'image_file_id' => $this->sampleFile('photo', '*', 90)?->id,
            'ordering'      => 0,
        ];
    }
}
