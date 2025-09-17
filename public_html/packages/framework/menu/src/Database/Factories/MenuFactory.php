<?php

namespace MetaFox\Menu\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Menu\Models\Menu;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class MenuFactory.
 * @method Menu create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class MenuFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Menu::class;

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
