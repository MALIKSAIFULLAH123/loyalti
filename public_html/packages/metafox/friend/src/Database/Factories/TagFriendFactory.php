<?php

namespace MetaFox\Friend\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Models\TagFriend;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class TagFriendFactory.
 *
 * @method TagFriend create($attributes = [], ?Model $parent = null)
 * @method self      setItem(Content $item)
 * @ignore
 * @codeCoverageIgnore
 */
class TagFriendFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TagFriend::class;

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
