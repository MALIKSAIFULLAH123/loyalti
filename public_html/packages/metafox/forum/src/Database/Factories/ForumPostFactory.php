<?php

namespace MetaFox\Forum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method ForumPost create($attributes = [], ?Model $parent = null)
 */
class ForumPostFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ForumPost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text = $this->faker->text(1000);

        return [
            'thread_id'   => $this->faker->numberBetween(1, 10),
            'user_id'     => 1,
            'user_type'   => 'user',
            'owner_id'    => 1,
            'owner_type'  => 'user',
            'is_approved' => 1,
            'text'        => $text,
            'text_parsed' => $text,
        ];
    }
}

// end
