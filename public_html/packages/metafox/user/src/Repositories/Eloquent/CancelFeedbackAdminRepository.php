<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Models\CancelFeedback as Model;
use MetaFox\User\Repositories\CancelFeedbackAdminRepositoryInterface;

/**
 * Class CancelFeedbackAdminRepository.
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 */
class CancelFeedbackAdminRepository extends AbstractRepository implements CancelFeedbackAdminRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function createFeedback(UserContract $context, array $attributes): Model
    {
        $userId   = Arr::get($attributes, 'user_id', 0);
        $feedback = $this->getModel()->newModelQuery()->where('user_id', '=', $userId)->first();

        if ($feedback instanceof Model) {
            return $feedback;
        }

        $feedback = new Model();
        $feedback->fill($attributes);
        $feedback->save();

        return $feedback;
    }

    public function viewFeedbacks(UserContract $context, array $attributes = []): Builder
    {
        if (!$context->hasPermissionTo('admincp.has_admin_access')) {
            throw new AuthorizationException();
        }

        $role   = Arr::get($attributes, 'role');
        $search = Arr::get($attributes, 'q');

        $query = $this->getModel()->newModelQuery();
        $table = $this->getModel()->getTable();

        if ($search) {
            $searchScope = new SearchScope();
            $searchScope->setFields(['email', 'name', 'phone_number'])->setSearchText($search);
            $query = $query->addScope($searchScope);
        }

        if ($role) {
            $query->where('user_group_id', '=', $role);
        }

        return $query->with('reason')->orderBy("$table.created_at", 'desc');
    }
}
