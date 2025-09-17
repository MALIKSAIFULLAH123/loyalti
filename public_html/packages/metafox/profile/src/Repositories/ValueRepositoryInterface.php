<?php

namespace MetaFox\Profile\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Value;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Value.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ValueRepositoryInterface
{
    /**
     * @param Field $field
     * @param array $value
     * @return void
     */
    public function deleteValue(Field $field, array $value): void;

    /**
     * @param User  $user
     * @param array $fieldIds
     * @return Collection
     */
    public function getValuesByFieldIds(User $user, array $fieldIds): Collection;

    /**
     * @param User $user
     * @return Collection
     */
    public function getValuesByUser(User $user): Collection;

    /**
     * @param Field $field
     * @param Value $value
     * @param array $attributes
     * @return mixed
     */
    public function handleFieldValue(Field $field, Value $value, array $attributes): mixed;

    /**
     * @param User  $user
     * @param array $attributes
     * @return Value
     */
    public function createValue(User $user, array $attributes): Value;
}
