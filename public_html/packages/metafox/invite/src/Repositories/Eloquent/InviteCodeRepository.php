<?php

namespace MetaFox\Invite\Repositories\Eloquent;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Invite\Models\InviteCode;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Invite\Support\Browse\Scopes\InviteCode\SortScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class InviteCodeRepository
 * @method InviteCode find($id, $columns = ['*'])
 * @method   InviteCode getModel()
 */
class InviteCodeRepository extends AbstractRepository implements InviteCodeRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return InviteCode::class;
    }

    public function createCode(User $context, ?CarbonInterface $expiredAt = null): InviteCode
    {
        if (null === $expiredAt) {
            return $this->createUserCode($context);
        }

        $attributes = [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'code'       => $this->generateUniqueCodeValue(),
            'is_active'  => MetaFoxConstant::IS_ACTIVE,
            'expired_at' => $expiredAt,
        ];

        $model = $this->getModel()->newInstance($attributes);

        $model->save();

        return $model->refresh();
    }

    /**
     * @inheritDoc
     */
    public function createUserCode(User $context): InviteCode
    {
        $attributes = [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'code'       => $this->generateUniqueCodeValue(),
            'is_active'  => MetaFoxConstant::IS_ACTIVE,
            'expired_at' => null,
        ];

        /**@var InviteCode $model */
        $model = $this->getModel()->newQuery()
            ->updateOrCreate([
                'user_id'    => $context->entityId(),
                'expired_at' => null,
            ], $attributes);

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function generateUniqueCodeValue(): string
    {
        do {
            $code = Str::random(8);
        } while (InviteCode::where('code', $code)->first());

        return $code;
    }

    /**
     * @param string $codeValue
     * @return InviteCode|null
     */
    public function verifyCodeByValue(string $codeValue): ?InviteCode
    {
        $model = $this->getCodeByValue($codeValue);
        if (!$model) {
            return null;
        }

        return $model;
    }

    /**
     * @warning This method only used for no expired invite code only
     * @param User $user
     * @return InviteCode
     */
    public function getUserCode(User $user): InviteCode
    {
        $attributes = [
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'code'       => $this->generateUniqueCodeValue(),
            'is_active'  => MetaFoxConstant::IS_ACTIVE,
            'expired_at' => null,
        ];

        /**@var InviteCode $model */
        $model = $this->getModel()->newQuery()
            ->firstOrCreate([
                'user_id'    => $user->entityId(),
                'expired_at' => null,
            ], $attributes);

        return $model;
    }

    /**
     * @param User   $user
     * @param string $inviteCode
     * @return InviteCode|null
     */
    public function getByCode(User $user, string $inviteCode): ?InviteCode
    {
        return $this->getModel()->newQuery()
            ->where([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'code'      => $inviteCode,
            ])
            ->first();
    }

    public function getCodeByValue(string $codeValue): ?InviteCode
    {
        $query = $this->getModel()->newQuery()
            ->where('code', $codeValue)
            ->first();

        if (!$query instanceof InviteCode) {
            return null;
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function viewUserCodes(array $params): Builder
    {
        $sort     = Arr::get($params, 'sort', Browse::SORT_RECENT);
        $sortType = Arr::get($params, 'sort_type', Browse::SORT_TYPE_DESC);
        $search   = Arr::get($params, 'q');
        $query    = $this->getModel()->newQuery();

        if ($search) {
            $query->leftJoin('users as su', 'su.id', '=', 'invite_codes.user_id')
                ->where('su.full_name', $this->likeOperator(), "%{$search}%")
                ->orWhere('su.user_name', $this->likeOperator(), "%{$search}%");
            $query->orWhere('code', $this->likeOperator(), "%{$search}%");
        }

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $query->whereNull('invite_codes.expired_at');

        return $query->addScope($sortScope);
    }
}
