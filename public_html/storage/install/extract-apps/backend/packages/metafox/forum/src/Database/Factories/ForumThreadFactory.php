<?php

namespace MetaFox\Forum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method ForumThread create($attributes = [], ?Model $parent = null)
 */
class ForumThreadFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ForumThread::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text = $this->faker->text(1000);
        return [
            'title'       => $this->faker->name,
            'forum_id'    => $this->faker->numberBetween(1, 9),
            'user_id'     => 1,
            'user_type'   => 'user',
            'owner_id'    => 1,
            'owner_type'  => 'user',
            'is_wiki'     => 0,
            'text'        => $text,
            'text_parsed' => $text,
        ];
    }
}

// end
