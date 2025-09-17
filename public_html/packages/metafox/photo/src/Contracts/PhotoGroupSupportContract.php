<?php
namespace MetaFox\Photo\Contracts;

use MetaFox\Photo\Models\PhotoGroup;

interface PhotoGroupSupportContract
{
    /**
     * @param PhotoGroup $group
     * @param bool       $isLoadForEdit
     * @param int|null   $limit
     * @return array
     */
    public function getMediaItems(\MetaFox\Photo\Models\PhotoGroup $group, bool $isLoadForEdit = false, ?int $limit = 4): array;
}
