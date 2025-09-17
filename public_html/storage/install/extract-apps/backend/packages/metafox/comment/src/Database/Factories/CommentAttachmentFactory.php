<?php

namespace MetaFox\Comment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class CommentAttachmentFactory.
 * @method CommentAttachment create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class CommentAttachmentFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CommentAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment_id' => 0, //set comment id
            'item_id'    => $this->sampleFile('*'),
            'item_type'  => CommentAttachment::TYPE_FILE,
        ];
    }

    /**
     * @param int $commentId
     *
     * @return CommentAttachmentFactory
     */
    public function setCommentId(int $commentId): self
    {
        return $this->state(function () use ($commentId) {
            return [
                'comment_id' => $commentId,
            ];
        });
    }
}

// end
