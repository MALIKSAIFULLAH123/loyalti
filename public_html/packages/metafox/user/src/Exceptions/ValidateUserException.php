<?php

namespace MetaFox\User\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Jsonable;
use Throwable;

/**
 * Class ValidateUserException.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ValidateUserException extends AuthorizationException implements Jsonable
{
    /**
     * Create a new authorization exception instance.
     *
     * @param  mixed           $message
     * @param  mixed           $code
     * @param  \Throwable|null $previous
     * @return void
     */
    public function __construct($message = null, $code = 401, Throwable $previous = null)
    {
        parent::__construct($this->toMessage($message), $code, $previous);
    }

    public function toJson($options = 0)
    {
        return $this->message;
    }

    /**
     * toMessage.
     *
     * @param  mixed  $message
     * @return string
     */
    private function toMessage($message): string
    {
        if (is_string($message)) {
            $message = [
                'title'   => __p('core::phrase.content_is_not_available'),
                'message' => $message,
            ];
        }

        return json_encode($message) ?: '';
    }
}
