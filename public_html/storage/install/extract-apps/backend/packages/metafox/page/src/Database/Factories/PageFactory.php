<?php

namespace MetaFox\Page\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class PageFactory.
 * @method Page create($attributes = [], ?Model $parent = null)
 */
class PageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text = $this->faker->text;

        $profileName = uniqid('page');

        return [
            'category_id'        => mt_rand(1, 100),
            'user_id'            => 1,
            'user_type'          => 'user',
            'privacy'            => 0,
            'name'               => $this->faker->company(),
            'profile_name'       => $profileName,
            'is_featured'        => 0,
            'is_sponsor'         => mt_rand(0, 1),
            'is_approved'        => 1,
            'location_latitude'  => 0.2,
            'location_longitude' => 0.2,
            'location_name'      => $this->faker->address,
            'location_address'   => $this->faker->address,
            'text'               => $text,
            'avatar_file_id'     => $this->sampleFile('photo', 'cover', 80)?->id,
            'cover_file_id'      => $this->sampleFile('photo', '*', 80)?->id,
        ];
    }

    public function setOwner(User $user): static
    {
        return $this;
    }

    public function seed()
    {
        return $this->afterCreating(function (Page $model) {
            PageMember::query()->create([
                'page_id'     => $model->id,
                'user_id'     => $model->user_id,
                'user_type'   => $model->user_type,
                'member_type' => 1,
            ]);
        });
    }
}
