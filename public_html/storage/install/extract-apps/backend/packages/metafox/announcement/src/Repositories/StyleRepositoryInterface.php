<?php

namespace MetaFox\Announcement\Repositories;

use MetaFox\Announcement\Models\Style;

/**
 * Interface StyleRepositoryInterface.
 *
 * @method Style getModel()
 * @method Style find($id, $columns = ['*'])
 */
interface StyleRepositoryInterface
{
    /**
     * @return array<int, mixed>
     */
    public function getStyleOptions(): array;
}
