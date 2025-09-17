<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Repositories\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use MetaFox\Core\Models\Privacy;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Membership;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PrivacyListRepositoryInterface.
 * @mixin BaseRepository
 * @method Privacy create(array $attributes)
 */
interface PrivacyRepositoryInterface
{
    /**
     * @param int    $itemId
     * @param string $itemType
     * @param string $privacyType
     *
     * @return int
     */
    public function getPrivacyId(int $itemId, string $itemType, string $privacyType): int;

    /**
     * Get privacy list for content resource.
     *
     * @param Content $content
     *
     * @return int[]
     */
    public function getPrivacyIdsForContent(Content $content): array;

    /**
     * Get privacy list for membership resource.
     *
     * @param Membership $membership
     *
     * @return int[]
     */
    public function getPrivacyIdsForMembership(Membership $membership): array;

    /**
     * Get privacy id for user privacy (user_privacy_values table).
     *
     * @param int $ownerId
     * @param int $privacy
     *
     * @return int
     */
    public function getPrivacyIdForUserPrivacy(int $ownerId, int $privacy): int;

    /**
     * @param  int         $ownerId
     * @param  int         $privacy
     * @return string|null
     */
    public function getPrivacyTypeByPrivacy(int $ownerId, int $privacy): ?string;

    /**
     * @param  Entity $model
     * @return void
     */
    public function forceCreatePrivacyStream(Entity $model): void;

    /**
     * @param  User      $context
     * @param  array     $params
     * @return Arrayable
     */
    public function getCustomPrivacyOptions(User $context, array $params = []): Arrayable;
}
