<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;

class GetPrivacyDetailListener
{
    protected FeedRepositoryInterface $repository;

    public function __construct(FeedRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User|null    $context
     * @param  Content|null $resource
     * @param  int|null     $representativePrivacy
     * @param  bool         $checkOwner
     * @return array|null
     */
    public function handle(?User $context, ?Content $resource, ?int $representativePrivacy = null, bool $checkOwner = false): ?array
    {
        return match ($checkOwner) {
            true  => $this->getPrivacyDetailOnOwner($context, $resource),
            false => $this->getPrivacyDetailForContent($context, $resource, $representativePrivacy),
        };
    }

    protected function getPrivacyDetailForContent(User $context, Content $resource, ?int $representativePrivacy = null): ?array
    {
        return $this->repository->getPrivacyDetail($context, $resource, $representativePrivacy);
    }

    protected function getPrivacyDetailOnOwner(User $context, Content $resource): ?array
    {
        if (!$resource instanceof User) {
            return $this->repository->getPrivacyDetail($context, $resource, $resource->owner?->getRepresentativePrivacy());
        }

        $owner = $resource;

        if ($resource->owner instanceof HasPrivacyMember) {
            $owner = $resource->owner;
        }

        return $this->repository->getOwnerPrivacyDetail($context, $owner, $owner->getRepresentativePrivacy());
    }
}
