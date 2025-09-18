<?php

namespace MetaFox\Marketplace\Database\Factories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MetaFox\Marketplace\Models\Category;

/**
 * Class Category.
 * @mixin Builder
 * @property int      $id
 * @property int      $parent_id
 * @property string   $name
 * @property string   $name_url
 * @property int      $is_active
 * @property int      $ordering
 * @property int      $total_item
 * @property string   $created_at
 * @property string   $updated_at
 * @property array    $subCategories
 * @method   Category create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->name;

        return [
            'name'       => $name,
            'name_url'   => Str::slug($name),
            'is_active'  => 1,
            'ordering'   => mt_rand(1, 10),
            'total_item' => 0,
        ];
    }
}
