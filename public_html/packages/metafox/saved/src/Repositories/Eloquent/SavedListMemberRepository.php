<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Saved\Models\SavedListMember;
use MetaFox\Saved\Repositories\SavedListMemberRepositoryInterface;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class SavedListMemberRepository.
 *
 * @method SavedListMember getModel()
 * @method SavedListMember find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class SavedListMemberRepository extends AbstractRepository implements SavedListMemberRepositoryInterface
{
    use UserMorphTrait;

    public function model(): string
    {
        return SavedListMember::class;
    }

    public function removeMember(User $context, int $listId, int $userId): bool
    {
        $member = $this->getModel()->newModelQuery()
            ->where('list_id', '=', $listId)
            ->where('user_id', '=', $userId)
            ->first();

        if ($member instanceof SavedListMember) {
            return $member->delete();
        }

        return false;
    }

    public function getInvitedUserIds(int $collectionId): array
    {
        return $this->getModel()->newQuery()
            ->where([
                'list_id' => $collectionId,
            ])
            ->get()
            ->pluck('user_id')
            ->toArray();
    }

    public function isSavedListMember(User $context, int $listId): bool
    {
        return $this->getModel()->newQuery()
            ->where('list_id', '=', $listId)
            ->where('user_id', '=', $context->entityId())
            ->exists();
    }

    /**
     * @param  User       $context
     * @param  int        $listId
     * @return Collection
     */
    public function viewSavedListMembers(User $context, int $listId): Collection
    {
        return $this->getModel()->newQuery()
            ->where('list_id', '=', $listId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function createMember(User $user, int $listId): Model
    {
        $data = [
            'list_id'   => $listId,
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
        ];

        return $this->getModel()->newQuery()->firstOrCreate($data);
    }
}
