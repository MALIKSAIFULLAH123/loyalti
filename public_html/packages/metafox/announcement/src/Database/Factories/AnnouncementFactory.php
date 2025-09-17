<?php

namespace MetaFox\Announcement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Style;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class AnnouncementFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class AnnouncementFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var Style $style */
        $style = Style::query()->firstOrFail();

        return [
            'user_id'           => 1,
            'user_type'         => 'user',
            'is_active'         => 1,
            'can_be_closed'     => 0,
            'show_in_dashboard' => 1,
            'start_date'        => $this->faker->dateTime,
            'country_iso'       => $this->faker->countryCode,
            'gender'            => 0,
            'age_from'          => rand(4, 10),
            'age_to'            => rand(30, 40),
            //            'user_group'        => serialize([1, 2, 3, 4, 5]),
            'gmt_offset'  => mt_rand(0, 23),
            'subject_var' => $this->faker->sentence,
            'intro_var'   => $this->faker->text,
            'style_id'    => $style->entityId(),
            // 'name_var'    => 'PHPUnit',
        ];
    }

    public function setStyle(Style $style): self
    {
        return $this->state(function () use ($style) {
            return ['style_id' => $style->entityId()];
        });
    }

    public function setOwner()
    {
        return $this;
    }
}
