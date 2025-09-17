<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UniqueSlug implements Rule
{
    /**
     * @var string
     */
    private string $type;

    /**
     * Exclude id check.
     * @var mixed
     */
    private mixed $id;

    private string $phrase = 'validation.unique';

    /**
     * @param string $type
     * @param mixed  $id
     */
    public function __construct(string $type, mixed $id = null)
    {
        $this->type = $type;
        $this->id   = $id;
    }

    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return false;
        }

        $result = app('events')
            ->dispatch('validation.unique_slug', [$this->type, $value, $this->id], true);

        if (null === $result) {
            return true;
        }

        if (is_string($result)) {
            $this->phrase = $result;
        }

        return false;
    }

    public function message()
    {
        return __p($this->phrase);
    }
}
