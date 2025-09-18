<?php

namespace MetaFox\Quiz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Quiz\Models\QuizPrivacyStream;

class QuizPrivacyStreamFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = QuizPrivacyStream::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}

// end
