<?php

namespace MetaFox\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Models\AttachmentFileType;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class AttachmentFileTypeFactory.
 * @method AttachmentFileType create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class AttachmentFileTypeFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttachmentFileType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'extension' => $this->faker->fileExtension(),
            'mime_type' => $this->faker->mimeType(),
            'is_active' => $this->faker->boolean,
        ];
    }
}

// end
