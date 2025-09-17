<?php

namespace MetaFox\Core\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Core\Models\AttachmentFileType as Model;

/**
 * Interface AttachmentFileType.
 * @mixin BaseRepository
 * @method AttachmentFileType getModel()
 * @method AttachmentFileType find($id, $columns = ['*'])
 */
interface AttachmentFileTypeRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewFileTypes(User $context, array $attributes = []): Paginator;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function createFileType(User $context, array $attributes = []): Model;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function updateFileType(User $context, int $id, array $attributes = []): Model;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteFileType(User $context, int $id): bool;
}
