<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class GroupFactory.
 * @method Group create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class GroupFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Group::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text        = $this->faker->text;
        $profileName =  uniqid('group');

        return [
            'category_id'        => 1,
            'user_id'            => 1,
            'user_type'          => 'user',
            'privacy'            => 0,
            'name'               => $this->faker->name,
            'text'               => $text,
            'cover_file_id'      => $this->sampleFile('photo', 'cover', 80)?->id,
            'is_featured'        => 0,
            'is_sponsor'         => 0,
            'total_member'       => 0,
            'location_latitude'  => 1,
            'location_longitude' => 1,
            'is_approved'        => 1,
            'profile_name'       => $profileName,
            'privacy_type'       => 0,
        ];
    }

    public function setOwner(User $user): static
    {
        return $this;
    }

    /**
     * @param int $privacyTypeId
     *
     * @return self
     */
    public function setPrivacyType(int $privacyTypeId): self
    {
        return $this->state(function () use ($privacyTypeId) {
            /** @var Group $model */
            $model = resolve($this->model);

            return [
                'privacy_type' => $privacyTypeId,
                'privacy'      => $model->getPrivacyTypeHandler()->getPrivacy($privacyTypeId),
                'privacy_item' => $model->getPrivacyTypeHandler()->getPrivacyItem($privacyTypeId),
            ];
        });
    }
}
