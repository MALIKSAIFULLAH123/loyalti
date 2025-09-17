<?php

namespace MetaFox\Announcement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Hidden;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class HiddenFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class HiddenFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Hidden::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id'         => null,
            'user_type'       => null,
            'announcement_id' => null,
        ];
    }

    public function setAnnouncement(Announcement $announcement): self
    {
        return $this->state(function () use ($announcement) {
            return ['announcement_id' => $announcement->entityId()];
        });
    }
}
