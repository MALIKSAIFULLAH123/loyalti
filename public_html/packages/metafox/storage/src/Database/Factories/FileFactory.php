<?php

namespace MetaFox\Storage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Storage\Models\StorageFile;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class FileFactory.
 * @method StorageFile create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class FileFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StorageFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'original_name' => $this->faker->name,
            'file_size' => $this->faker->numberBetween(),
            'mime_type' => $this->faker->mimeType(),
            'extension' => $this->faker->fileExtension(),
            'width'     => $this->faker->numberBetween(100, 2000),
            'height'    => $this->faker->numberBetween(100, 2000),
            'path'      => $this->faker->filePath(),
        ];
    }
}

// end
