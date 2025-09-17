<?php

namespace MetaFox\Forum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThreadLastRead;
use MetaFox\Platform\Support\Factory\HasSetState;

class ForumThreadLastReadFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ForumThreadLastRead::class;

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
            'thread_id'  => 1,
            'post_id'    => 1,
            'created_at' => $this->faker->dateTimeBetween('-10days', 'now'),
        ];
    }

    public function seed()
    {
        return $this->state(function () {
            /** @var ForumPost $post */
            $post = ForumPost::all()->random();

            return [
                'post_id'   => $post->id,
                'thread_id' => $post->thread_id,
            ];
        });
    }
}
