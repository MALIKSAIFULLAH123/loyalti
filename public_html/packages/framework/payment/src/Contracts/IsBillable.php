<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Interface IsBillable.
 * @mixin Model
 */
interface IsBillable extends Entity
{
    /**
     * toOrder.
     *
     * @return array<string,mixed>
     */
    public function toOrder(): array;

    /**
     * order.
     *
     * @return MorphOne
     */
    public function order(): MorphOne;

    /**
     * @return User|null
     */
    public function payee(): ?User;
}
