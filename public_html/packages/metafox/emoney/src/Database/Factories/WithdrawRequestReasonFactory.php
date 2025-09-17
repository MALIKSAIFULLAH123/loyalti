<?php

namespace MetaFox\EMoney\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\EMoney\Models\WithdrawRequestReason;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class WithdrawRequestReasonFactory.
 * @method WithdrawRequestReason create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class WithdrawRequestReasonFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WithdrawRequestReason::class;

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
