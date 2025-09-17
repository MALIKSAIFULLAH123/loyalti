<?php

namespace MetaFox\Search\Repositories\Eloquent;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Search\Contracts\TypeManager;
use MetaFox\Search\Models\Type;
use MetaFox\Search\Policies\TypePolicy;
use MetaFox\Search\Repositories\TypeRepositoryInterface;

/**
 * Class TypeRepository.
 *
 * @method Type find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class TypeRepository extends AbstractRepository implements TypeRepositoryInterface
{
    public function model()
    {
        return Type::class;
    }

    public function updateType(User $context, int $id, array $attributes): Type
    {
        $resource = $this->find($id);

        policy_authorize(TypePolicy::class, 'update', $context, $resource);

        $resource->fill($attributes);

        $abilities = $resource->getAbilities();

        if (!empty($abilities)) {
            foreach ($abilities as $ability => $key) {
                if (!isset($attributes[$ability])) {
                    continue;
                }
                $value = $attributes[$ability];
                $resource->setFlag($key, $value);
            }
        }

        $resource->save();

        $activityTypeManager = resolve(TypeManager::class);

        $activityTypeManager->refresh();

        return $resource;
    }

    public function deleteType(User $context, int $id): int
    {
        $resource = $this->find($id);

        policy_authorize(TypePolicy::class, 'delete', $context, $resource);

        $response = $this->delete($id);

        $activityTypeManager = resolve(TypeManager::class);
        $activityTypeManager->refresh();

        return $response;
    }
}
