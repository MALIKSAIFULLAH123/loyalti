<?php

namespace MetaFox\Hashtag\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MetaFox\Hashtag\Models\Tag;

/**
 * Class TagFactory.
 * @method Tag create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hashtag = $this->faker->word;

        return [
            'text'    => $hashtag,
            'tag_url' => Str::lower(Str::slug($hashtag)),
        ];
    }

    public function setText($text): self
    {
        return $this->state(function () use ($text) {
            return [
                'text'    => $text,
                'tag_url' => Str::lower(Str::slug($text)),
            ];
        });
    }
}
