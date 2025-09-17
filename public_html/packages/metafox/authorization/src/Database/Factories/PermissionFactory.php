<?php

namespace MetaFox\Authorization\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class PermissionFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 */
class PermissionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $module = 'test';
        $action = $this->faker->word;

        return [
            'name'        => $module . $action,
            'guard_name'  => 'api',
            'module_id'   => $module,
            'entity_type' => 'test',
            'action'      => $action,
        ];
    }
}

// end
