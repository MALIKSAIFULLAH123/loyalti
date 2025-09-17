<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Traits;

use MetaFox\Core\Models\PrivacyMember;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;

/**
 * Trait IsPrivacyItemTrait.
 */
trait IsPrivacyItemTrait
{
    use PreparePrivacyRepositoryTrait;

    public function handlePrivacyItemForCreated(IsPrivacyItemInterface $model): void
    {
        $items = $model->toPrivacyItem();

        if (!empty($items)) {
            foreach ($items as $data) {
                [$userId, $itemId, $itemType, $privacyType] = $data;

                $privacyId = $this->privacyRepository()
                    ->getPrivacyId($itemId, $itemType, $privacyType);

                if ($privacyId) {
                    $this->privacyMemberRepository()->firstOrCreate([
                        'user_id'    => $userId,
                        'privacy_id' => $privacyId,
                    ]);
                }
            }
        }
    }

    public function handlePrivacyItemForDeleted(IsPrivacyItemInterface $model): void
    {
        $items = $model->toPrivacyItem();
        foreach ($items as $item) {
            [$userId, $itemId, $itemType, $privacyType] = $item;
            $privacyId                                  = $this->privacyRepository()->getPrivacyId($itemId, $itemType, $privacyType);
            if (!$privacyId) {
                continue;
            }
            // Find to populated into model to use observe, then call delete method.
            $privacyMember = $this->privacyMemberRepository()->getModel()
                ->where('privacy_id', '=', $privacyId)
                ->where('user_id', '=', $userId)
                ->first();
            if ($privacyMember instanceof PrivacyMember) {
                $privacyMember->delete();
            }
        }
    }
}
