<?php

namespace MetaFox\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Models\Link;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class LinkFactory.
 * @method Link create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class LinkFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Link::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $url   = $this->faker->url;
        $title = $this->faker->sentence;

        return [
            'privacy'      => MetaFoxPrivacy::EVERYONE,
            'feed_content' => $title,
            'title'        => $title,
            'link'         => $url,
            'host'         => parse_url($url, PHP_URL_HOST),
            'image'        => faker_image_path('photo'),
            'description'  => $title,
            'has_embed'    => 0,
        ];
    }
}

// end
