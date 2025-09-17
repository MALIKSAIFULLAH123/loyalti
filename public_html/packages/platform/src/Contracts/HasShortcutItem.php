<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasShortcutItem.
 */
interface HasShortcutItem extends Entity
{
    /**
     * [title, image, item_type_name, total_photo, user(UserEntity), link].
     * @return array<string, mixed>
     */
    public function toShortcutItem(): array;
}
