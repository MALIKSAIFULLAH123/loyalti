<?php

namespace MetaFox\Form;

use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;

/**
 * Trait PrivacyFieldTrait.
 */
trait PrivacyFieldTrait
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
            return Builder::hidden('privacy');
        }

        return Builder::privacy();
    }

    protected function buildPrivacyMobileField(): AbstractField
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return MobileBuilder::hidden('privacy');
        }

        return MobileBuilder::privacy();
    }
}
