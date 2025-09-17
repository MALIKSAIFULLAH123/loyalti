<?php

namespace MetaFox\Platform\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use Symfony\Component\HttpFoundation\ParameterBag;

class EnsureParseNestedArray
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string $separator
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $separator = MetaFoxConstant::NESTED_ARRAY_SEPARATOR): mixed
    {
        $this->transform($request, $separator);

        return $next($request);
    }

    /**
     * @param  Request $request
     * @param  string  $separator
     * @return void
     */
    protected function transform(Request $request, string $separator): void
    {
        $this->transformParameterBag($request->query, $separator);

        if ($request->isJson()) {
            $this->transformParameterBag($request->json(), $separator);
        } elseif ($request->request !== $request->query) {
            $this->transformParameterBag($request->request, $separator);
        }
    }

    /**
     * @param  ParameterBag $bag
     * @param  string       $separator
     * @return void
     */
    protected function transformParameterBag(ParameterBag $bag, string $separator)
    {
        $bag->replace($this->transformArray($bag->all(), $separator));
    }

    /**
     * @param  array<string, mixed> $data
     * @param  string               $separator
     * @return array<string, mixed>
     */
    protected function transformArray(array $data, string $separator): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $subKeys = explode($separator, $key);
            if (count($subKeys) <= 1) {
                $result[$key] = $value;
                continue;
            }

            $newKey = implode('.', $subKeys);
            $result[$newKey] = $value;
        }

        return Arr::undot($result);
    }
}
