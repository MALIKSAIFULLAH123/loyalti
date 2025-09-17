<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueEmail implements Rule
{
    /**
     * Exclude id check.
     * @var mixed
     */
    private mixed $id;

    private string $phrase = 'validation.unique';

    /**
     * @param mixed $id
     */
    public function __construct(mixed $id = null)
    {
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return false;
        }

        $found = DB::selectOne(
            'select id from users where lower(email)=lower(?) LIMIT 1',
            [$value]
        );

        if (!$found) {
            return true;
        }

        if ($found->id == $this->id) {
            return true;
        }

        return false;
    }

    public function message()
    {
        return __p($this->phrase);
    }
}
