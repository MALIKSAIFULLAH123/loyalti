<?php

namespace MetaFox\Profile\Repositories;

use MetaFox\Profile\Models\Field;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Option.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface OptionRepositoryInterface
{
    /**
     * @param Field $field
     * @param array $attributes
     * @return void
     */
    public function createOptions(Field $field, array $attributes): void;

    /**
     * @param Field $field
     * @param array $attributes
     * @return void
     */
    public function updateOptions(Field $field, array $attributes): void;

    /**
     * @param Field $field
     * @param array $attributes
     * @return void
     */
    public function removeOptions(Field $field, array $attributes): void;

    /**
     * @return mixed
     */
    public function getAllOptions(): mixed;
}
