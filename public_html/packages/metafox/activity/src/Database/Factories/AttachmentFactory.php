<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Activity\Models\Attachment;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class AttachmentFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class AttachmentFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'    => 1,
            'user_type'  => 'user',
            'owner_id'   => 1,
            'owner_type' => 'user',
            'privacy'    => 0,
        ];
    }
}

// end
