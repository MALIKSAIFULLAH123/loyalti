<?php

namespace MetaFox\Core\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface AttachmentFileTypeContract
 * @package MetaFox\Core\Contracts
 */
interface AttachmentFileTypeContract
{
    /**
     * Get all attachment types
     *
     * @return Collection
     */
    public function getAttachmentFileTypes(): Collection;

    /**
     * Get active attachment types
     *
     * @return Collection
     */
    public function getAllActive(): Collection;

    /**
     * Get all file mime of active types
     *
     * @return string[]
     */
    public function getAllMineTypeActive(): array;

    /**
     * Get all file extensions of active types
     * @return string[]
     */
    public function getAllExtensionActive(): array;
}
