<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property Content|null $item
 * @package MetaFox\Platform\Contracts
 */
interface HasItemMorph
{
    /**
     * @return MorphTo|null
     */
    public function item(): ?morphTo;

    /**
     * @return string
     */
    public function itemType(): string;

    /**
     * @return int
     */
    public function itemId(): int;
}
