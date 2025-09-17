<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\UserRelation;

/**
 * Class UserRelationFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 * @method UserRelation create($attributes = [], ?Model $parent = null)
 */
class UserRelationFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserRelation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phrase_var' => 'example',
            'confirm'    => 0,
        ];
    }
}

// end
