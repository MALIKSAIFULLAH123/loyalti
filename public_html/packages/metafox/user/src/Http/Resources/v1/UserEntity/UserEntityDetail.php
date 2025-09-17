<?php

namespace MetaFox\User\Http\Resources\v1\UserEntity;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Models\UserEntity as Model;
use MetaFox\User\Support\Browse\Traits\User\ExtraTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Traits\UserLocationTrait;
use MetaFox\User\Traits\UserStatisticTrait;
use stdClass;

/**
 * Class UserDetail.
 *
 * @property Model|stdClass $resource
 */
class UserEntityDetail extends JsonResource
{
    use UserLocationTrait;
    use UserStatisticTrait;
    use ExtraTrait;

    /**
     * Special case when user is deleted and the relation is null
     * Laravel auto return null if $resource property is null
     * so need to override to bypass default behavior of Laravel.
     *
     * @param Model|null $resource
     */
    public function __construct(mixed $resource)
    {
        if (null === $resource) {
            $resource = new stdClass();
        }

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        if (!$this->resource instanceof Model) {
            return $this->getDeletedUserResource();
        }

        $context = user();
        $isDeleted = $this->resource->isDeleted();
        $detail = $this->resource->detail;

        if (!$detail instanceof ContractUser) {
            return $this->getDeletedUserResource();
        }

        $friendship = UserFacade::getFriendship($context, $detail);
        $detailProfile = $detail instanceof HasUserProfile ? $detail->profile : $detail;

        $data = [
            'id'               => $this->resource->entityId(),
            'module_name'      => $this->resource->entityType(),
            'resource_name'    => $this->resource->entityType(),
            'full_name'        => $this->when($isDeleted, __p('core::phrase.deleted_user'), $this->resource->name),
            'display_name'     => $this->when($isDeleted, __p('core::phrase.deleted_user'), $this->resource->display_name),
            'user_name'        => $this->when($isDeleted, null, $this->resource->user_name),
            'avatar_id'        => $this->when($isDeleted, 0, (int)$this->getAvatarId($context, $this->resource)),
            'avatar'           => $this->when($isDeleted, null, $this->resource->avatars),
            'cover'            => $this->when($isDeleted, null, fn() => $this->getResourceCovers($detail)),
            'cover_photo_id'   => $this->when($isDeleted, 0, $detailProfile instanceof HasCoverMorph ? $detailProfile->cover_id : 0),
            'is_featured'      => $this->when($isDeleted, null, $this->resource->is_featured),
            'short_name'       => $this->when($isDeleted, null, $this->resource->short_name),
            'friendship'       => $this->when($isDeleted, null, $friendship),
            'link'             => $this->when($isDeleted, null, $this->resource->toLink()),
            'url'              => $this->when($isDeleted, null, $this->resource->toUrl()),
            'router'           => $this->when($isDeleted, null, $this->resource->toRouter()),
            'location'         => $this->toLocation($context),
            'profile_settings' => UserPrivacy::hasAccessProfileSettings($context, $detail),
            'is_deleted'       => $isDeleted,
            'statistic'        => $this->getResourceStatistics($request),
        ];

        $extraAttributes = $this->getExtraAttributes($context);
        $data = array_merge($data, $extraAttributes);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getResourceStatistics(?Request $request): array
    {
        $default = $this->getStatistic();

        $detail = $this->resource->detail;

        if (null === $detail) {
            return $default;
        }

        $resource = ResourceGate::asEmbed($detail);

        if (null === $resource) {
            return $default;
        }

        $resource = $resource->toArray($request);

        $statistics = Arr::get($resource, 'statistic', []);

        return array_merge($default, $statistics);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDeletedUserResource(): array
    {
        $id = sprintf('deleted_user_%s_%s', Str::random(12), Carbon::now()->timestamp);

        return [
            'id'            => $id,
            'module_name'   => 'user',
            'resource_name' => 'user',
            'full_name'     => __p('core::phrase.deleted_user'),
            'user_name'     => $id,
            'avatar'        => null,
            'is_featured'   => null,
            'short_name'    => null,
            'friendship'    => null,
            'link'          => null,
            'url'           => null,
            'router'        => null,
            'location'      => null,
            'is_deleted'    => true,
            'statistic'     => [],
        ];
    }

    /**
     * @param ContractUser $user
     *
     * @return array<string, mixed>|null
     */
    protected function getResourceCovers(ContractUser $user): ?array
    {
        if ($user instanceof HasCoverMorph) {
            return $user->covers;
        }

        $profile = $user instanceof HasUserProfile ? $user->profile : null;

        return $profile?->covers;
    }

    /**
     * This method shall be removed from version 5.2
     * Temporary solution for mobile app to avoid crash.
     *
     * @param ContractUser $context
     * @param Model|null $user
     *
     * @return int|null
     * @deprecated 5.2
     */
    protected function getAvatarId(ContractUser $context, ?Model $user): ?int
    {
        $isMobile = MetaFox::isMobile();

        $avatarId = $user?->avatar_id;

        $isViewOwnProfile = $context->entityId() && $context->entityId() === $user?->entityId();

        return $avatarId ?: ($isMobile && !$isViewOwnProfile ? -1 : 0);
    }

    protected function toLocation(ContractUser $context): mixed
    {
        if ($this->resource->isDeleted()) {
            return null;
        }

        $detail = $this->resource->detail;

        if (method_exists($detail, 'toLocationObject')) {
            return $detail->toLocationObject();
        }

        return $this->getLocationValue($context, $detail);
    }
}
