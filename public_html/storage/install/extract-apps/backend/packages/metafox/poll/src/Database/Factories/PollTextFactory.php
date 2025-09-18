<?php

namespace MetaFox\Poll\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Poll\Models\PollText;

class PollTextFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PollText::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text'        => $this->faker->text,
            'text_parsed' => $this->faker->text,
        ];
    }
}

// end
