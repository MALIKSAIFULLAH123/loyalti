<?php

namespace MetaFox\Announcement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Announcement\Models\Style;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class StyleFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class StyleFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Style::class;

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
