<?php

namespace MetaFox\Saved\Repositories;

use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Saved\Models\Saved;

interface SavedSearchRepositoryInterface
{
    /**
     * @param  HasSavedItem $item
     * @return void
     */
    public function createdBy(HasSavedItem $item): void;

    /**
     * @param  HasSavedItem $item
     * @return void
     */
    public function updatedBy(HasSavedItem $item): void;

    /**
     * @param  Saved $item
     * @return bool
     */
    public function isSearchExist(Saved $item): bool;
}
