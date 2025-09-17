<?php

namespace MetaFox\Form;

use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;

/**
 * Trait PrivacyFieldMobileTrait.
 */
trait PrivacyFieldMobileTrait
{
    public ?User $owner = null;

    public function setOwner(?User $owner = null): self
    {
        $this->owner = $owner;

        return $this;
    }

    protected function buildPrivacyField(): AbstractField
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return \MetaFox\Form\Mobile\Builder::hidden('privacy');
        }

        return \MetaFox\Form\Mobile\Builder::privacy();
    }
}
