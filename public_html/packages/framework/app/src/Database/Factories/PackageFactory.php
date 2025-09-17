<?php

namespace MetaFox\App\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MetaFox\App\Models\Package;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class ModuleFactory.
 * @method Package create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Package::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $alias = $this->faker->userName;
        return [
            'name'           => 'metafox/' . $alias,
            'icon'           => 'ico-app',
            'title'          => $this->faker->sentence,
            'author'         => 'metafox',
            'author_url'     => 'https://metafox.app',
            'path'           => 'packages/metafox/' . $alias,
            'namespace'      => 'metafox/' . $alias,
            'name_studly'    => Str::studly($alias),
            'alias'          => $alias,
            'version'        => '5.0.1',
            'providers'      => [],
            'type'           => 'app',
            'category'       => null,
            'latest_version' => '5.0.1',
            'keywords'       => $this->faker->sentence,
            'description'    => $this->faker->sentence,
            'requires'       => [],
            'is_active'      => false,
            'is_installed'   => false,
            'frontend'       => [],
            'mobile'         => false,
        ];
    }
}

// end
