<?php

namespace MetaFox\Platform\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Ban\Facades\Ban;
use Exception;
use Illuminate\Http\JsonResponse;

class UserBanStatus
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        try {
            Ban::validateMultipleType(Auth::user());

            return $next($request);
        } catch (Exception $exception) {
            return $this->handleException($exception, $exception->getCode() ?: 427);
        }
    }

    /**
     * Handle the exception and return a JSON response.
     *
     * @param  Exception    $exception
     * @param  int          $code
     * @return JsonResponse
     */
    protected function handleException(Exception $exception, int $code): JsonResponse
    {
        $message = $exception->getMessage();

        if (is_string($message)) {
            $decodedMessage = json_decode($message, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $message = Arr::get($decodedMessage, 'message', $message);
            }
        }

        return response()->json(['message' => $message], $code);
    }
}
