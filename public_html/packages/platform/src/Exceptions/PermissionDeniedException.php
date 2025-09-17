<?php

namespace MetaFox\Platform\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Entity;

class PermissionDeniedException extends AuthorizationException
{
    /**
     * Create a new authorization exception instance.
     *
     * @param  string|null     $message
     * @param  mixed           $code
     * @param  \Throwable|null $previous
     * @return void
     */
    public function __construct($message = null, $code = null)
    {
        parent::__construct($message ?? 'This action is unauthorized.', $code ?? 403);
    }

    /**
     * The context data contains mostly.
     *
     * @var array<string, mixed> | array<int, mixed>
     */
    private array $contextData;

    /**
     * @param  array<string, mixed>|array<int, mixed> $data
     * @return void
     */
    public function setContextData(array $data = []): void
    {
        $this->contextData = $data;
    }

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    public function getContextData(): array
    {
        return $this->contextData;
    }

    public function toMetaData(?Request $request = null): array
    {
        $data = $this->normalizedData($request);

        return [
            'title'       => __p('core::phrase.permission_deny_exception_message_title', $data),
            'description' => __p('core::phrase.permission_deny_exception_message_description', $data),
        ];
    }

    public function normalizedData(?Request $request = null): array
    {
        $params = [];

        collect($this->contextData)->each(function ($item) use (&$params) {
            if ($item instanceof Entity) {
                $params['resource_id']   = $item->entityId();
                $params['resource_type'] = $item->entityType();
            }
        });

        $resourceType = Arr::get($params, 'resource_type', 'core');
        $policyMethod = Arr::get($params, 'policy_method', 'none');

        Arr::set($params, 'permission_for', sprintf('%s::%s', $resourceType, $policyMethod));

        if ($request) {
            // Support request context data
            $requestData = $request->collect()
                ->dot()
                ->filter(fn ($data) => is_string($data) && !empty($data))
                ->chunk(20)
                ->first()
                ?->toArray();

            return is_array($requestData) ? array_merge($requestData, $params) : $params;
        }

        return $params;
    }
}
