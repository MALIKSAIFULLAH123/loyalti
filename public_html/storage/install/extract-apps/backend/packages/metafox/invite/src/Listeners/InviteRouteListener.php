<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Policies\InvitePolicy;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class InviteRouteListener.
 * @ignore
 * @codeCoverageIgnore
 */
class InviteRouteListener
{
    public function __construct(protected InviteRepositoryInterface $repository, protected InviteCodeRepositoryInterface $codeRepository) {}

    /**
     * @param string $url
     *
     * @return array<string,mixed>|null
     */
    public function handle(string $url, ?string $route = null, ?array $queryParams = null): ?array
    {
        if (!Str::startsWith($url, 'invite/ref')) {
            return null;
        }

        if (null === $queryParams) {
            $queryParams = $this->parseQueryParams($url);
        }

        if (is_string($code = Arr::get($queryParams, 'code'))) {
            return $this->handleInvite($code);
        }

        if (is_string($code = Arr::get($queryParams, 'invite_code'))) {
            return $this->handleInviteCode($code);
        }

        return $this->inviteOnly() ? null : ['path' => '/register'];
    }

    private function parseQueryParams(string $url): array
    {
        $parts = parse_url($url);

        $queryString = Arr::get($parts, 'query', MetaFoxConstant::EMPTY_STRING);

        $queryParams = [];

        if (is_string($queryString)) {
            parse_str($queryString, $queryParams);
        }

        return $queryParams;
    }

    protected function handleInviteCode(string $code): ?array
    {
        $inviteCode = $this->codeRepository->getCodeByValue($code);
        $inviter    = $inviteCode?->user;

        if (!$inviter instanceof User || !policy_check(InvitePolicy::class, 'create', $inviter)) {
            return $this->inviteOnly() ? null : ['path' => "/register?invite_code={$code}"];
        }

        return [
            'path' => "/register?invite_code={$code}",
        ];
    }

    protected function handleInvite(string $code): ?array
    {
        $invite = $this->repository->getModel()->newQuery()
            ->where('code', $code)
            ->first();

        if (!$invite instanceof Invite) {
            return $this->inviteOnly() ? null : ['path' => "/register?code={$code}"];
        }

        $inviter = $invite?->user;
        if (!$inviter instanceof User || !policy_check(InvitePolicy::class, 'create', $inviter)) {
            return $this->inviteOnly() ? null : ['path' => "/register?code={$code}"];
        }

        return [
            'path' => "/register?code={$code}&invite_code={$invite->invite_code}",
        ];
    }

    protected function inviteOnly(): bool
    {
        return Settings::get('invite.invite_only', false);
    }
}
