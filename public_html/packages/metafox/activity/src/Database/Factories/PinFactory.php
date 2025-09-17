<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Pin;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeed;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class PinFactory.
 * @codeCoverageIgnore
 * @ignore
 */
class PinFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => 1,
            'user_type' => 'user',
            'feed_id'   => 1,
        ];
    }

    /**
     * @param Content $item
     *
     * @return self
     */
    public function setItem(HasFeed $item): self
    {
        return $this->state(function () use ($item) {
            $feed = $item->activity_feed;
            if (!$feed instanceof Feed) {
                abort(500);
            }

            return [
                'feed_id' => $feed->entityId(),
            ];
        });
    }
}

// end
