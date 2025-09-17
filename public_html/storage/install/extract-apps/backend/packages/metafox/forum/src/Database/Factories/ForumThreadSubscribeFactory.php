<?php

namespace MetaFox\Forum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Models\ForumThreadSubscribe;

class ForumThreadSubscribeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ForumThreadSubscribe::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-10days', 'now');

        return [
            'user_id'    => 1,
            'user_type'  => 'user',
            'item_id'    => 1,
            'created_at' => $createdAt,
        ];
    }

    public function seed()
    {
        return $this->state(function () {
            $thread = ForumThread::all()->random()->value('id');

            return [
                'item_id' => $thread,
            ];
        });
    }
}
