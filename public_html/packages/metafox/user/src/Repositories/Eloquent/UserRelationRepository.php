<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Jobs\DeletedRelationJob;
use MetaFox\User\Models\UserRelation;
use MetaFox\User\Repositories\UserRelationRepositoryInterface;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

class UserRelationRepository extends AbstractRepository implements UserRelationRepositoryInterface
{
    public function model()
    {
        return UserRelation::class;
    }

    public function getPhraseRepository(): PhraseRepositoryInterface
    {
        return resolve(PhraseRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function viewRelationShips(User $user, array $attributes): Paginator
    {
        $limit  = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $search = Arr::get($attributes, 'q');

        $query = $this->getModel()->newModelQuery()->select('user_relation.*');

        $defaultLocale = Language::getDefaultLocaleId();

        if ($search) {
            $searchScope = new SearchScope(
                $search,
                ['user_relation.phrase_var', 'ps.text']
            );
            $searchScope->setTableField('phrase_var');
            $searchScope->setJoinedTable('phrases');
            $searchScope->setAliasJoinedTable('ps');
            $searchScope->setJoinedField('key');
            $query->where('ps.locale', '=', $defaultLocale);
            $query = $query->addScope($searchScope);
        }

        return $query->paginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function createRelationShip(User $user, array $attributes): UserRelation
    {
        $fileId = null;
        if ($attributes['temp_file'] > 0) {
            $tempFile = upload()->getFile($attributes['temp_file']);
            $fileId   = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($attributes['temp_file']);
        }

        Arr::set($attributes, 'image_file_id', $fileId);
        $relation = new UserRelation();
        $relation->fill($attributes)->save();

        return $relation;
    }

    /**
     * @inheritDoc
     */
    public function activeRelation(int $id): UserRelation
    {
        $item = $this->find($id);

        $item->update(['is_active' => $item->is_active ? 0 : 1]);

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getRelations(): Collection
    {
        return $this->getModel()->newQuery()
            ->where('is_active', 1)->get();
    }

    /**
     * @inheritDoc
     */
    public function updateRelationShip(User $user, array $attributes): UserRelation
    {
        $id       = Arr::get($attributes, 'id');
        /** @var UserRelation $relation */
        $relation = $this->find($id);

        $fileId = null;
        if ($attributes['temp_file'] > 0) {
            $tempFile = upload()->getFile($attributes['temp_file']);
            $fileId   = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($attributes['temp_file']);
        }

        Arr::set($attributes, 'image_file_id', $fileId);

        $relation->fill($attributes)->save();

        return $relation->refresh();
    }

    /**
     * @inheritDoc
     */
    public function deleteRelation(User $context, int $id): bool
    {
        $relation = $this->find($id);

        if (!$relation instanceof UserRelation || !$relation->is_custom) {
            abort(401, __p('phrase.permission_deny'));
        }

        $this->getPhraseRepository()->deleteWhere(['key' => $relation->phrase_var]);

        DeletedRelationJob::dispatch($relation->entityId());

        return (bool) $relation->delete();
    }
}
