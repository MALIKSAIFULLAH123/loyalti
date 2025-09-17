<?php

namespace MetaFox\Friend\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Models\FriendListData;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class FriendListDataFactory.
 * @method FriendListData create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendListDataFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FriendListData::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'list_id' => 1,
        ];
    }

    /**
     * @param int $listId
     *
     * @return self
     */
    public function setListId(int $listId): self
    {
        return $this->state(function () use ($listId) {
            return [
                'list_id' => $listId,
            ];
        });
    }
}
