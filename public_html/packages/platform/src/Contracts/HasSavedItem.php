<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasSavedItem.
 *
 * @TODO Implement toSavedItemWithContext() in version 5.1.8.2
 */
interface HasSavedItem extends Entity
{
    /**
     * [title, image, item_type_name, total_photo, user(UserEntity), link].
     * @return array<string, mixed>
     */
    public function toSavedItem(): array;
}
