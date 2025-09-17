<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;

/**
 * Class ParseSharingRouteListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ParseSharingRouteListener
{
    public GroupInviteCodeRepositoryInterface $codeRepository;

    public function __construct(GroupInviteCodeRepositoryInterface $codeRepository)
    {
        $this->codeRepository = $codeRepository;
    }

    /**
     * @param string $url
     *
     * @return array<string,mixed>|null
     */
    public function handle(string $url): ?array
    {
        if (!Str::startsWith($url, 'group/invite')) {
            return null;
        }

        $code = Arr::last(explode('/', $url));

        $inviteCode = $this->codeRepository->getCodeByValue($code, 1);
        if (!$inviteCode instanceof GroupInviteCode) {
            return null;
        }

        $group = $inviteCode->group;
        if (!$group instanceof Group) {
            return null;
        }

        return [
            'path' => sprintf('/group/invite/%s', $inviteCode->entityId()),
        ];
    }
}
