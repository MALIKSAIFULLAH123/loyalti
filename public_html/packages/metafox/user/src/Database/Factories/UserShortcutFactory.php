<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\UserShortcut;

/**
 * Class UserShortcutFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 * @method UserShortcut create($attributes = [], ?Model $parent = null)
 */
class UserShortcutFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserShortcut::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => 1,
            'user_type' => 'user',
            'item_id'   => 1,
            'item_type' => 'user',
            'sort_type' => $this->faker->boolean(50) ? 0 : 1,
        ];
    }

    public function seed()
    {
        return $this->state(function ($attributes) {
            /** @var User $page */
            $page = \MetaFox\Page\Models\Page::query()->inRandomOrder()->first();

            return [
                'item_id'   => $page->entityId(),
                'item_type' => $page->entityType(),
            ];
        });
    }
}

// end
