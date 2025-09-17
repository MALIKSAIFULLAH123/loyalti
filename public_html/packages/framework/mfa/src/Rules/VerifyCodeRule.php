<?php

namespace MetaFox\Mfa\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class UserRelationRule.
 */
class VerifyCodeRule implements Rule, DataAwareRule
{
    /**
     * @var array
     */
    protected array       $data = [];
    protected string      $message;
    protected FormRequest $request;

    public function __construct(FormRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Set the data under validation.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getMessage(): string
    {
        if ($this->message == MetaFoxConstant::EMPTY_STRING) {
            return __p('mfa::phrase.the_token_does_not_exist');
        }

        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param $attribute
     * @param $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        try {
            if (Mfa::authenticate($this->request)) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
        }
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->getMessage();
    }
}
