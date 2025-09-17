<?php

namespace MetaFox\Page\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Models\PageClaim;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method PageClaim create($attributes = [], ?Model $parent = null)
 */
class PageClaimFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PageClaim::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message'   => $this->faker->text,
            'status_id' => 0,
        ];
    }
}
