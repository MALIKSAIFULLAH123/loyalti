<?php

namespace MetaFox\Platform\Contracts;

use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;

/**
 * Interface HasPrivacy.
 *
 * Determine a resource has privacy field.
 * @mixin AppendPrivacyListTrait
 *
 * @property int $privacy
 */
interface HasPrivacy
{
}
