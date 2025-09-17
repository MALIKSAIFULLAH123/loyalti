<?php

namespace MetaFox\Menu\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class MenuItemFactory.
 * @method MenuItem create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class MenuItemFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MenuItem::class;

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
