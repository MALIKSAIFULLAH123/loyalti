<?php

namespace MetaFox\User\Http\Resources\v1\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Core\Support\Facades\Timezone as TimezoneFacade;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class User.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AccountSetting extends JsonResource
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
        // Timezone.
        $timezoneId = $this->resource?->profile?->timezone_id;
        $languageId = $this->resource?->profile?->language_id;
        $currencyId = $this->resource?->profile?->currency_id;

        return [
            'resource_name'     => 'account',
            'user_name'         => $this->resource?->user_name,
            'full_name'         => $this->resource?->full_name_raw,
            'last_name'         => $this->resource?->last_name ?? UserFacade::getLastName($this->resource?->full_name),
            'first_name'        => $this->resource?->first_name ?? UserFacade::getFirstName($this->resource?->full_name),
            'email'             => $this->resource?->email,
            'language_id'       => $languageId,
            'language_name'     => Language::getName($languageId),
            'phone_number'      => $this->resource?->phone_number,
            'timezone_id'       => $timezoneId,
            'timezone_name'     => TimezoneFacade::getName($timezoneId),
            'currency_id'       => $currencyId,
            'currency_name'     => app('currency')->getName($currencyId),
            'module_name'       => $this->resource?->entityType(),
            'id'                => $this->resource?->entityId(),
            'link'              => $this->resource?->toLink(),
            'url'               => $this->resource?->toUrl(),
            'extra'             => $this->getExtra(),
            'privacy'           => MetaFoxPrivacy::EVERYONE,
            'creation_date'     => $this->resource?->created_at,
            'modification_date' => $this->resource?->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getExtra(): array
    {
        /*
         * Migrated from policy_check to using Gate::allows.
         * $canDelete = policy_check(UserPolicy::class, 'delete', $this->resource, $this->resource);
         */
        $canDelete = Gate::allows('delete', $this->resource);

        return [
            'can_delete_account' => !$this->resource->hasSuperAdminRole() && $canDelete,
        ];
    }
}
