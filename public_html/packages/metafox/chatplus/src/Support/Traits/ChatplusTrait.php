<?php

namespace MetaFox\ChatPlus\Support\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\UserRole;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;
use MetaFox\User\Models\UserBlocked as BlockedModel;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Support\Facades\UserBlocked;

trait ChatplusTrait
{
    public ?Collection $usableRoles = null;

    public function getChatplusRole(int $role): array
    {
        return match ((string) $role) {
            '1', '2' => ['admin', 'user'],
            '3'     => ['staff'],
            '4'     => ['user'],
            '5'     => ['guest'],
            '6'     => ['banned'],
            default => ["role-$role"],
        };
    }

    public function getChatplusParentRole(Role $role): string
    {
        $parentId    = $role->parent_id;
        $validParent = false;
        $roles       = $this->getUsableRoles();
        $roles->each(function ($role) use ($parentId, &$validParent) {
            if (!$role instanceof Role) {
                return;
            }
            if ($role->id == $parentId) {
                $validParent = true;
            }
        });
        if (!$validParent) {
            $parentId = UserRole::NORMAL_USER;
        }
        $parentRoleName = Arr::first($this->getChatplusRole($parentId));
        if (Str::contains($parentRoleName, 'role-')) {
            // Custom role
            try {
                return $this->getChatplusParentRole(Role::query()->find($parentId));
            } catch (\Exception) {
                return UserRole::NORMAL_USER;
            }
        }

        return $parentRoleName;
    }

    public function getUsableRoles(): Collection
    {
        if (!$this->usableRoles) {
            $this->usableRoles = $this->getRoleRepository()->getUsableRoles();
        }

        return $this->usableRoles;
    }

    public function getChatplusChildRoles(): array
    {
        $roles       = $this->getUsableRoles();
        $customRoles = [];
        $roles->each(function ($role) use (&$customRoles) {
            if (!$role instanceof Role) {
                return;
            }
            if (in_array($role->id, [UserRole::ADMIN_USER, UserRole::NORMAL_USER, UserRole::STAFF_USER, UserRole::SUPER_ADMIN_USER, UserRole::GUEST_USER, UserRole::BANNED_USER])) {
                return; // Ignore default role
            }
            $parentRoleName = $this->getChatplusParentRole($role);
            if (!Arr::exists($customRoles, $parentRoleName)) {
                $customRoles[$parentRoleName] = [];
            }
            $customRoles[$parentRoleName][] = 'role-' . $role->id;
        });

        return $customRoles;
    }

    public function getRoleRepository(): RoleRepositoryInterface
    {
        return resolve(RoleRepositoryInterface::class);
    }

    public function getChatplusAvatar(?UserProfile $profile, string $suffix): string
    {
        $avatars = $profile?->avatars ?? [];
        $result  = null;
        if (count($avatars)) {
            foreach (array_keys($avatars) as $sf) {
                if ($sf == $suffix) {
                    $result = $suffix;
                    break;
                }
            }
            $result = $result ?? 'origin';
        }

        return $result ? $avatars[$result] : '';
    }

    public function canMessage(?User $owner, ?User $user): bool
    {
        $canCreateDirectMessage = false;
        $isAppActive            = app_active('metafox/chatplus') && Settings::get('chatplus.server');
        $ownerId                = $owner instanceof User ? $owner->getAuthIdentifier() : null;
        $userId                 = $user instanceof User ? $user->getAuthIdentifier() : null;

        // add new hook for checking can message options.
        //

        if ($isAppActive && $ownerId && $userId && $userId != $ownerId) {
            $visibility = Settings::get('chatplus.chat_visibility');

            $isFriend               = app('events')->dispatch('friend.is_friend', [$ownerId, $userId], true);
            $isBlocked              = UserBlocked::isBlocked($owner, $user);
            $isBlocker              = UserBlocked::isBlocked($user, $owner);
            $canCreateDirectMessage = !$isBlocked && !$isBlocker && $user->isApproved() && $user->hasVerified();
            if ($canCreateDirectMessage && $visibility === 'friendship') {
                $canCreateDirectMessage = $isFriend;
            }
        }

        return $canCreateDirectMessage;
    }

    public function getAllBlockUsers(User $user): array
    {
        return BlockedModel::query()
            ->where(function (Builder $builder) use ($user) {
                $builder->orWhere('user_id', $user->entityId())
                    ->orWhere('owner_id', $user->entityId());
            })
            ->select(DB::raw('CASE user_id WHEN ' . $user->entityId() . ' THEN owner_id ELSE user_id END as blocked_id'))
            ->pluck('blocked_id')
            ->toArray();
    }

    public function validateRequest(Request $request): bool
    {
        $token       = $request->get('token');
        $privateCode = Settings::get('chatplus.private_code');

        if (!$token || !$privateCode) {
            return false;
        }

        return $token == $privateCode;
    }

    public function getAssetRepository(): AssetRepositoryInterface
    {
        return resolve(AssetRepositoryInterface::class);
    }
}
