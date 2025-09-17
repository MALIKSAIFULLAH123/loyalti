<?php

namespace MetaFox\Notification\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Support\TypeManager;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class TypeFactory.
 * @method Type create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class TypeFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Type::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(5);

        return [
            'type'       => $title,
            'title'      => $title,
            'module_id'  => 'test',
            'can_edit'   => 1,
            'is_request' => 0,
            'is_active'  => 1,
            'is_system'  => 1,
            'channels'   => ['database', 'mail'],
        ];
    }

    public function seed()
    {
        return $this->afterCreating(function () {
            resolve(TypeManager::class)->refresh();
        });
    }
}

// end
