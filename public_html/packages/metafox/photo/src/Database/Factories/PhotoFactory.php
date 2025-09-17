<?php

namespace MetaFox\Photo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class PhotoFactory.
 * @method self  setUser(User $user)()
 * @method self  setOwner(User $user)()
 * @method self  setCustomPrivacy(array $list = [])
 * @method Photo create($attributes = [], ?Model $parent = null)
 */
class PhotoFactory extends Factory
{
    use HasSetState;

    /** @var string */
    protected $model = Photo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'         => $this->faker->sentence,
            'item_type'     => 'photo',
            'privacy'       => random_privacy(),
            'user_id'       => 1,
            'user_type'     => 'user',
            'owner_id'      => 1,
            'owner_type'    => 'user',
            'categories'    => $this->faker->shuffleArray([1, 2, 3, 4]),
            'content'       => $this->faker->paragraph(rand(1, 3)),
            'image_file_id' => $this->sampleFile('jpeg')?->id,
            'is_approved'   => 1,
            'total_like'    => 0,
            'total_share'   => 0,
            'total_comment' => 0,
        ];
    }

    protected function callAfterCreating(Collection $instances, ?Model $parent = null)
    {
        parent::callAfterCreating($instances, $parent);

        $instances->each(function (Photo $model) {
            $model->loadMissing('activity_feed');
        });
    }
}
