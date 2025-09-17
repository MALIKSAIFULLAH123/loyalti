<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Share;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method Share create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class ShareFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Share::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'    => 1,
            'user_type'  => 'user',
            'owner_id'   => 1,
            'owner_type' => 'user',
            'item_id'    => 1,
            'item_type'  => 'activity_post',
            'privacy'    => 0,
        ];
    }

    /**
     * @param Feed $feed
     *
     * @return self
     */
    public function setParentFeed(Feed $feed): self
    {
        return $this->state(function () use ($feed) {
            return [
                'parent_feed_id'   => $feed->entityId(),
                'parent_module_id' => $feed->item ? $feed->item->entityType() : 'feed',
            ];
        });
    }
}

// end
