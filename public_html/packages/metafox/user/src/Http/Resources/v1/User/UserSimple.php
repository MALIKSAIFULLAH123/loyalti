<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Models\UserVerify;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class UserDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserSimple extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $profile = $this->resource->profile;

        return [
            'id'                    => $this->resource->entityId(),
            'full_name'             => $this->resource->full_name,
            'display_name'         => $this->resource->display_name,
            'avatar'                => $profile->avatars,
            'resource_name'         => $this->resource->entityType(),
            'profile_page_id'       => 0,
            'user_name'             => $this->resource->user_name,
            'email'                 => $this->resource->email,
            'phone_number'          => $this->resource->phone_number,
            'short_name'            => UserFacade::getShortName($this->resource->full_name),
            'link'                  => $this->resource->toLink(),
            'url'                   => $this->resource->toUrl(),
            'router'                => $this->resource->toRouter(),
            'is_deleted'            => $this->resource->isDeleted(),
            'is_verified'           => $this->resource->hasVerified(),
            'verification_required' => $this->getVerifyRequired(),
        ];
    }

    protected function getVerifyRequired(): ?array
    {
        $mustVerifyEmail       = $this->resource->mustVerifyEmailAddress();
        $mustVerifyPhoneNumber = $this->resource->mustVerifyPhoneNumber();

        $action = $mustVerifyEmail ? UserVerify::ACTION_EMAIL
            : ($mustVerifyPhoneNumber ? UserVerify::ACTION_PHONE_NUMBER : null);

        if ($action === null) {
            return null;
        }

        return [
            'action'       => $action,
            'user_id'      => $this->resource->entityId(),
            'email'        => $mustVerifyEmail ? $this->resource->email : null,
            'phone_number' => $mustVerifyPhoneNumber ? $this->resource->phone_number : null,
        ];
    }
}
