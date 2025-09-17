<?php

namespace MetaFox\User\Support\Browse\Traits\User;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\User\Models\UserEntity;

trait ExtraTrait
{
    use HasExtra {
        getExtra as getMainExtra;
    }

    public function getExtra($resolution = null): array
    {
        if (empty($this->resource)) {
            return [];
        }

        $permissions = $this->getMainExtra($resolution);
        $context = user();
        $extraPermissions = app('events')->dispatch('user.permissions.extra', [$context, $this->resource, $resolution]);

        if (is_array($extraPermissions)) {
            foreach ($extraPermissions as $extraPermission) {
                if (is_array($extraPermission) && count($extraPermission)) {
                    $permissions = array_merge($permissions, $extraPermission);
                }
            }
        }

        return $permissions;
    }

    public function getExtraAttributes(User $context): array
    {
        $data = [];
        $resource = $this->resource;
        
        if ($resource instanceof UserEntity) {
            $resource = $this->resource->detail;
        }

        if (!$resource instanceof User) {
            return $data;
        }

        $extraAttributes = app('events')->dispatch('user.attributes.extra', [$context, $resource]);

        if (!is_array($extraAttributes)) {
            return $data;
        }

        foreach ($extraAttributes as $extraAttribute) {
            if (is_array($extraAttribute) && count($extraAttribute)) {
                $data = array_merge($data, $extraAttribute);
            }
        }

        return $data;
    }
}
