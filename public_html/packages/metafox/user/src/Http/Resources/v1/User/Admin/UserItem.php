<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\ResourcePermission;
use MetaFox\User\Support\ResourcePermission as UserResourcePermission;
use MetaFox\User\Models\User;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Models\UserActivity;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class UserItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $ipAddress    = '';
        $lastActivity = '';
        $lastLogin    = '';

        $userActivity = $this->resource->userActivity;

        if ($userActivity instanceof UserActivity) {
            $ipAddress    = $userActivity->last_ip_address;
            $lastActivity = $userActivity->last_activity;
            $lastLogin    = $userActivity->last_login;
        }

        $userProfile = $this->resource->profile;

        return [
            'id'             => $this->resource->entityId(),
            'module_name'    => 'user',
            'resource_name'  => $this->resource->entityType(),
            'full_name'      => $this->resource->full_name,
            'display_name'   => $this->resource->display_name,
            'user_name'      => $this->resource->user_name,
            'avatar'         => $userProfile?->avatars,
            'user'           => ResourceGate::user($this->resource->userEntity),
            'role_name'      => $this->resource->transformRole(),
            'email'          => $this->resource->email,
            'phone_number'   => $this->resource->phone_number,
            'user_link'      => $this->resource->userEntity?->toUrl(),
            'created_at'     => $this->resource->created_at,
            'ip_address'     => $ipAddress,
            'last_activity'  => $lastActivity,
            'last_login'     => $lastLogin,
            'is_approved'    => $this->resource->isApproved(),
            'approve_status' => $this->resource->approve_status,
            'is_featured'    => $this->resource->is_featured,
            'is_mfa_enabled' => app('events')->dispatch('user.user_mfa_enabled', [$this->resource], true),
            'is_banned'      => UserFacade::isBan($this->resource->entityId()),
            'is_verified'    => $this->resource->hasVerified(),
            'country_name'   => Country::getCountryName($userProfile?->country_iso),
            'extra'          => $this->getExtra(),
            'links'          => [
                'editItem' => '/user/user/edit/' . $this->resource->entityId(),
            ],
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function getExtra()
    {
        $policy = PolicyGate::getPolicyFor(User::class);
        if (!$policy instanceof UserPolicy) {
            abort(400, 'Missing Policy');
        }

        $context = user();

        return [
            ResourcePermission::CAN_EDIT                    => $policy->manage($context, $this->resource),
            ResourcePermission::CAN_FEATURE                 => $policy->feature($context, $this->resource),
            ResourcePermission::CAN_DELETE                  => $policy->delete($context, $this->resource),
            UserResourcePermission::CAN_BAN                 => $policy->banUser($context, $this->resource),
            UserResourcePermission::CAN_VERIFY              => $this->resource->shouldVerifyEmailAddress() || $this->resource->shouldVerifyPhoneNumber(),
            UserResourcePermission::CAN_RESEND_EMAIL        => $this->resource->shouldVerifyEmailAddress(),
            UserResourcePermission::CAN_RESEND_PHONE_NUMBER => $this->resource->shouldVerifyPhoneNumber(),
        ];
    }
}
