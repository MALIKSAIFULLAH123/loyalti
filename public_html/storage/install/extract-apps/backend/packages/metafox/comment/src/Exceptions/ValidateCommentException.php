<?php

namespace MetaFox\Comment\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Class ValidateCommentException.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ValidateCommentException extends Exception implements Jsonable
{
    /**
     * @param  mixed $message
     * @param  mixed $code
     * @return void
     */
    public function __construct(string $message, $code = 422)
    {
        parent::__construct($this->toMessage($message), $code);
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
                'title'   => __p('comment::validation.the_post_has_been_removed_title'),
                'message' => $message,
            ];
        }

        return json_encode($message) ?: '';
    }
}
