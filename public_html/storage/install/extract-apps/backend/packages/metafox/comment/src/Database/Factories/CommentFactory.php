<?php

namespace MetaFox\Comment\Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class CommentFactory.
 * @method Comment create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class CommentFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $content = $this->faker->paragraph(mt_rand(1, 4));

        return [
            'owner_id'      => 1,
            'owner_type'    => 'user',
            'user_id'       => $this->randomUserId(),
            'user_type'     => 'user',
            'item_id'       => '1',
            'item_type'     => 'blog',
            'is_approved'   => 1,
            'parent_id'     => 0,
            'total_comment' => 0,
            'total_like'    => 1,
            'text'          => $content,
            'text_parsed'   => $content,
            'created_at'    => Carbon::now(),
        ];
    }

    public function setUser(User $user): self
    {
        return $this->state(function () use ($user) {
            return [
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
            ];
        });
    }

    public function setItem(Content $item): self
    {
        return $this->state(function () use ($item) {
            return [
                'item_id'    => $item->entityId(),
                'item_type'  => $item->entityType(),
                'owner_id'   => $item->userId(),
                'owner_type' => $item->userType(),
            ];
        });
    }

    public function seed()
    {
        return $this->state(function () {
            $item = $this->pickRandomContent();

            return [
                'item_id'    => $item->entityId(),
                'item_type'  => $item->entityType(),
                'owner_id'   => $item->ownerId(),
                'owner_type' => $item->ownerType(),
            ];
        });
    }

    public function configure()
    {
        if ($this->faker->boolean(5)) {
            $this->has(CommentAttachment::factory()->count(1), 'commentAttachment');
        }

        return $this;
    }
}
