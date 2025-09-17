<?php

namespace MetaFox\User\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasShortcutItem;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\User\Models\UserShortcut;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface UserShortcut.
 * @mixin BaseRepository
 */
interface UserShortcutRepositoryInterface
{
    /**
     * Browse users.
     *
     * @param ContractUser         $context
     * @param array<string, mixed> $attributes
     *
     * @return Collection
     *
     * @throws AuthorizationException
     */
    public function viewShortcuts(ContractUser $context, array $attributes): Collection;

    /**
     * Browse users.
     *
     * @param ContractUser         $context
     * @param array<string, mixed> $attributes
     *
     * @return Collection
     *
     * @throws AuthorizationException
     */
    public function viewForEdit(ContractUser $context, array $attributes): Collection;

    /**
     * Update sort type.
     *
     * @param ContractUser $context
     * @param Content      $content
     * @param int          $sortType
     *
     * @return UserShortcut
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function updateShortType(ContractUser $context, Content $content, int $sortType): UserShortcut;

    /**
     * @param HasShortcutItem $item
     * @return void
     */
    public function createdBy(HasShortcutItem $item): void;

    /**
     * @param HasShortcutItem $item
     * @return void
     */
    public function deletedBy(HasShortcutItem $item): void;

    /**
     * @param ContractUser $item
     * @return void
     */
    public function deletedByItem(ContractUser $item): void;
}
