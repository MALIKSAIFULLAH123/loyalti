<?php

namespace MetaFox\Hashtag\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Hashtag\Models\TagData;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class TagDataFactory.
 * @method TagData create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class TagDataFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TagData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }

    public function setTagId(int $tagId): self
    {
        return $this->state(function () use ($tagId) {
            return [
                'tag_id' => $tagId,
            ];
        });
    }
}
