<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;

/**
 * Class AbstractFeedAction.
 */
class AbstractFeedAction
{
    /**
     * @var string
     */
    protected string $type;

    /**
     * @var Content
     */
    protected Content $source;

    public function getUserId(): int
    {
        return $this->source->userId();
    }

    /**
     * @return string
     */
    public function getUserType(): string
    {
        return $this->source->userType();
    }

    public function getOwnerId(): int
    {
        return $this->source->ownerId();
    }

    public function getOwnerType(): string
    {
        return $this->source->ownerType();
    }

    public function getItemId(): int
    {
        return $this->source->entityId();
    }

    public function getItemType(): string
    {
        return $this->source->entityType();
    }

    public function getPrivacy(): int
    {
        if ($this->source instanceof HasPrivacy) {
            return $this->source->privacy ?? 0;
        }

        return 0;
    }

    public function getTypeId(): string
    {
        return $this->source->entityType();
    }

    public function getContent(): ?string
    {
        return null;
    }
}
