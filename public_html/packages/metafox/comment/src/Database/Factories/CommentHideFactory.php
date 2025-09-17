<?php

namespace MetaFox\Comment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Comment\Models\CommentHide;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class CommentHideFactory.
 * @method CommentHide create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class CommentHideFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CommentHide::class;

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

    public function setItem(Content $item)
    {
        return $this->state(function () use ($item) {
            return [
                'item_id' => $item->entityId(),
            ];
        });
    }
}

// end
