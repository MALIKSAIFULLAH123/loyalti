<?php

namespace MetaFox\Event\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\User;

class EventFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $text = $this->faker->paragraph(mt_rand(1, 3));

        $localCoordinates = $this->faker->localCoordinates;

        return [
            'name'               => $this->faker->sentence,
            'start_time'         => $this->faker->dateTimeBetween('now', '+10 days'),
            'end_time'           => $this->faker->dateTimeBetween('+11', '+60 days'),
            'is_online'          => 0,
            'event_url'          => null,
            'module_id'          => 'event',
            'view_id'            => 0,
            'location_latitude'  => $localCoordinates['latitude'],
            'location_longitude' => $localCoordinates['longitude'],
            'location_name'      => $this->faker->city,
            'country_iso'        => $this->faker->countryCode,
            'user_id'            => 1,
            'user_type'          => User::ENTITY_TYPE,
            'owner_id'           => 1,
            'owner_type'         => User::ENTITY_TYPE,
            'is_sponsor'         => random_value(5, 1, 0),
            'sponsor_in_feed'    => random_value(5, 1, 0),
            'is_featured'        => random_value(5, 1, 0),
            'is_approved'        => 1,
            'privacy'            => 0,
            'created_at'         => $this->faker->dateTime,
            'updated_at'         => $this->faker->dateTime,
            'text'               => $text,
            'categories'         => [1, 2, 3],
            'image_file_id'      => $this->sampleFile('photo', '*', 90)?->id,
        ];
    }

    public function seed()
    {
        return $this->afterCreating(function (Event $event) {
            Member::query()->create([
                'event_id'  => $event->id,
                'user_id'   => $event->user_id,
                'user_type' => $event->user_type,
                'role_id'   => 1,
                'rsvp_id'   => 1,
            ]);
        });
    }
}
