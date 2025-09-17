<?php

namespace MetaFox\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Blog\Models\Blog;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class BlogFactory.
 * @method Blog        create($attributes = [], ?Model $parent = null)
 * @method BlogFactory setCustomPrivacy(array $list = [])
 * @ignore
 * @codeCoverageIgnore
 */
class BlogFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Blog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text = $this->faker->text(1000);

        return [
            'title'            => $this->faker->sentence,
            'privacy'          => random_privacy(),
            'module_id'        => 'blog',
            'user_id'          => 1,
            'user_type'        => 'user',
            'owner_id'         => 1,
            'owner_type'       => 'user',
            'text'             => $text,
            'categories'       => [1, 2],
            'is_approved'      => 1,
            'is_draft'         => 0,
            'total_like'       => 0,
            'total_share'      => 0,
            'total_comment'    => 0,
            'total_attachment' => 0,
            'total_view'       => 0,
            'image_file_id'    => $this->sampleFile('photo', '*', 80)?->id,
        ];
    }

    public function seed()
    {
        return $this->state(fn () => [
            'total_view'  => $this->faker->numberBetween(0, 5e4),
            'total_share' => $this->faker->numberBetween(0, 5e4),
            'is_draft'    => $this->faker->boolean(10),
            'is_approved' => $this->faker->boolean(90),
        ]);
    }
}
