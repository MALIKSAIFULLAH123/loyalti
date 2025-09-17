<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class FeedAction.
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class FeedAction
{
    /**
     * @var int
     */
    private $user_id;

    /**
     * @var string
     */
    private $user_type;

    /**
     * @var int
     */
    private $owner_id;

    /**
     * @var string
     */
    private $owner_type;

    /**
     * @var int
     */
    private $item_id;

    /**
     * @var string
     */
    private $item_type;

    /**
     * @var int
     */
    private $privacy = 0;

    /**
     * @var string
     */
    private $type_id;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string|null
     */
    private $fromResource;

    /**
     * @var array<string,mixed>
     */
    private $extra;

    /**
     * FeedAction constructor.
     *
     * @param array<mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $keys = [
            'user_id'       => 'setUserId',
            'user_type'     => 'setUserType',
            'owner_id'      => 'setOwnerId',
            'owner_type'    => 'setOwnerType',
            'item_id'       => 'setItemId',
            'item_type'     => 'setItemType',
            'type_id'       => 'setTypeId',
            'privacy'       => 'setPrivacy',
            'content'       => 'setContent',
            'status'        => 'setStatus',
            'from_resource' => 'setFromResource',
        ];

        foreach ($keys as $key => $method) {
            if (isset($attributes[$key])) {
                $this->{$method}($attributes[$key]);
            }
        }

        $this->extra = Arr::except($attributes, $keys);
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): void
    {
        $this->user_id = $userId;
    }

    /**
     * @return string
     */
    public function getUserType(): string
    {
        return $this->user_type;
    }

    public function setUserType(string $userType): void
    {
        $this->user_type = $userType;
    }

    public function getOwnerId(): int
    {
        return $this->owner_id;
    }

    public function setOwnerId(int $ownerId): void
    {
        $this->owner_id = $ownerId;
    }

    public function getOwnerType(): string
    {
        return $this->owner_type;
    }

    public function setOwnerType(string $ownerType): void
    {
        $this->owner_type = $ownerType;
    }

    public function getItemId(): int
    {
        return $this->item_id;
    }

    public function setItemId(int $itemId): void
    {
        $this->item_id = $itemId;
    }

    public function getItemType(): string
    {
        return $this->item_type;
    }

    public function setItemType(string $itemType): void
    {
        $this->item_type = $itemType;
    }

    public function getPrivacy(): int
    {
        return $this->privacy;
    }

    public function setPrivacy(int $privacy): void
    {
        $this->privacy = $privacy;
    }

    public function getTypeId(): string
    {
        return $this->type_id;
    }

    public function setTypeId(string $typeId): void
    {
        $this->type_id = $typeId;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getFromResource(): ?string
    {
        return $this->fromResource;
    }

    public function setFromResource(?string $fromResource): void
    {
        $this->fromResource = $fromResource;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status ?? MetaFoxConstant::ITEM_STATUS_APPROVED;
    }

    /**
     * @param  string $status
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }
}
