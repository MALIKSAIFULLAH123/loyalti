<?php

namespace MetaFox\Notification\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Notification\Models\Type;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Throwable;

/**
 * Interface Type.
 *
 * @mixin BaseRepository
 */
interface TypeRepositoryInterface
{
    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $attributes
     *
     * @return Collection
     */
    public function viewTypes(array $attributes): Collection;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Type
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function updateType(User $context, int $id, array $attributes): Type;

    /**
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function deleteType(User $context, int $id): int;

    /**
     * @param User   $context
     * @param string $channel
     *
     * @return array<int, mixed>
     */
    public function getNotificationSettingsByChannel(User $context, string $channel): array;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return bool
     */
    public function updateNotificationSettingsByChannel(User $context, array $attributes): bool;

    /**
     * @return array
     */
    public function getAllNotificationType(): array;

    /**
     * @param string $type
     *
     * @return Type|null
     */
    public function getNotificationTypeByType(string $type): ?Type;
}
