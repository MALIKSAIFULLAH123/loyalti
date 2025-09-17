<?php

namespace MetaFox\StaticPage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\StaticPage\Models\StaticPage;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class StaticPageFactory.
 * @method StaticPage create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class StaticPageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StaticPage::class;

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
