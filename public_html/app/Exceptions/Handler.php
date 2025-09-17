<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Laravel\Passport\Exceptions\OAuthServerException as PassportOAuthServerException;
use League\OAuth2\Server\Exception\OAuthServerException;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Traits\Fox4JsonResponse;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use Fox4JsonResponse;

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        OAuthServerException::class,
        PassportOAuthServerException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //        $this->reportable(function (Throwable $e) {
        //            //
        //        });
    }

    protected function exceptionContext(Throwable $e)
    {
        $context             = parent::exceptionContext($e);
        $context['Trace-ID'] = sprintf('%s', spl_object_id($e));

        return $context;
    }

    public function render($request, Throwable $e)
    {
        // Handle abort(code, message) for api response.
        if ($e instanceof NotFoundHttpException) {
            return $this->error(__p('core::phrase.content_is_not_available'), 404);
        }

        if ($e instanceof HttpException) {
            return $this->error($e->getMessage(), $e->getStatusCode());
        }

        if ($e instanceof Jsonable) {
            return $this->error($e->toJson(), 403);
        }

        // Handling unauthorized request when an access token expired
        if ($e instanceof AuthenticationException) {
            return $this->error('', 401, ['refresh_retry' => 1]);
        }

        if ($e instanceof PermissionDeniedException) {
            $meta = $e->toMetaData($request);
            $this->addMetadata($meta);

            return $this->error($meta['title'] ?? '', 403);
        }

        if ($e instanceof AuthorizationException) {
            return $this->error(__p('core::phrase.content_is_not_available'), 403);
        }

        if ($e instanceof ModelNotFoundException) {
            $model = $e->getModel();
            $model = (new $model());
            if ($model instanceof Entity) {
                return $this->error(
                    __p(
                        'core::phrase.the_entity_name_you_are_looking_for_can_not_be_found',
                        ['entity_name' => str_replace('_', ' ', $model->entityType())]
                    ),
                    ResponseAlias::HTTP_NOT_FOUND
                );
            }
        }

        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        return parent::render($request, $e);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Throwable $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        $debugTraceId = sprintf('%s', spl_object_id($e));

        return config('app.debug')
            ? [
                'message'      => $e->getMessage(),
                'exception'    => get_class($e),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
                'trace'        => collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
                'debugTraceId' => $debugTraceId,
            ]
            : [
                'message'      => $this->isHttpException($e) ? $e->getMessage() : __p('validation.something_went_wrong_please_try_again'),
                'debugTraceId' => $debugTraceId,
            ];
    }
}
