<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface Entity.
 * @property string $admin_browse_url
 * @property string $admin_edit_url
 * @method   string getAdminBrowseUrlAttribute()
 * @method   string getAdminEditUrlAttribute()
 */
interface Entity
{
    /**
     * @return int
     */
    public function entityId(): int;

    /**
     * @return string
     */
    public function entityType(): string;

    /**
     * @return string
     */
    public function moduleName(): string;
}
