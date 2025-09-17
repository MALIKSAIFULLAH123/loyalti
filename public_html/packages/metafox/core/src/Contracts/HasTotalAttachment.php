<?php

namespace MetaFox\Core\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;

/**
 * @property int        $total_attachment
 * @property Collection $attachments
 * @mixin Model
 */
interface HasTotalAttachment extends Entity, HasAmounts
{
    /**
     * @return MorphMany
     */
    public function attachments(): MorphMany;

    /**
     * @return array<int, mixed>
     */
    public function attachmentsForForm(): array;
}
