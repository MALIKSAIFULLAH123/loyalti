<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Activity\Models\AttachmentData;
use MetaFox\Platform\Contracts\Content;

/**
 * Class AttachmentDataFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class AttachmentDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttachmentData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    /**
     * @param Content $item
     *
     * @return AttachmentDataFactory
     */
    public function setItem(Content $item)
    {
        return $this->state(function () use ($item) {
            return [
                'item_id'   => $item->entityId(),
                'item_type' => $item->entityType(),
            ];
        });
    }

    /**
     * @param int $attachmentId
     *
     * @return AttachmentDataFactory
     */
    public function setAttachmentId($attachmentId)
    {
        return $this->state(function () use ($attachmentId) {
            return [
                'attachment_id' => $attachmentId,
            ];
        });
    }
}

// end
