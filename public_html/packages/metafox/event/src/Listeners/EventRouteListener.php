<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Core\Models\UrlRewrite;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Event\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class EventRouteListener.
 * @ignore
 * @codeCoverageIgnore
 */
class EventRouteListener
{
    private InviteCodeRepositoryInterface $codeRepository;

    public function __construct(InviteCodeRepositoryInterface $codeRepository)
    {
        $this->codeRepository = $codeRepository;
    }

    /**
     * @param string $url
     *
     * @return array<string,mixed>|void
     */
    public function handle(string $url, ?string $route = null, ?array $queryParams = null)
    {
        if (!Str::startsWith($url, 'event/invite')) {
            return;
        }

        if (null === $route) {
            [$route, $queryParams] = $this->parseUrl($url);
        }

        $code = Arr::last(explode('/', $route));

        $inviteCode = $this->codeRepository->getCodeByValue($code, 1);

        if (!$inviteCode instanceof InviteCode) {
            return;
        }

        $event = $inviteCode->event;

        if (!$event instanceof Event) {
            return;
        }

        $path = "/{$event->entityType()}/{$event->entityId()}?invite_code={$inviteCode->code}";

        if (Arr::has($queryParams, 'stab')) {
            $path .= sprintf('&stab=%s', Arr::get($queryParams, 'stab'));
        }

        return [
            'path' => $path,
        ];
    }

    private function parseUrl(string $url): array
    {
        $parts = parse_url($url);

        $route = Arr::get($parts, 'path', MetaFoxConstant::EMPTY_STRING);

        $queryString = Arr::get($parts, 'query', MetaFoxConstant::EMPTY_STRING);

        $queryParams = [];

        if (is_string($queryString)) {
            parse_str($queryString, $queryParams);
        }

        return [$route, $queryParams];
    }
}
