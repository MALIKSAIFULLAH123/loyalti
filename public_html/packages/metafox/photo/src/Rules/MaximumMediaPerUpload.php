<?php

namespace MetaFox\Photo\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator;

class MaximumMediaPerUpload implements RuleContract
{
    private int $maxFiles;

    public function __construct(int $maxFiles = 0)
    {
        $this->maxFiles = $maxFiles;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        // This rule is applied when value is an array, other case will not applicable
        if (!is_array($value)) {
            return true;
        }

        $count      = collect($value)->groupBy('status')->map(function ($item) {
            return count($item);
        })->get('new');

        $count = $count ?? 0;

        // Only when user sent a number of files then we should validate
        if ($this->maxFiles === 0 || $count === 0) {
            return true;
        }

        $validator = Validator::make(['count' => $count], [
            'count' => ['integer', 'max:' . $this->maxFiles],
        ]);

        return $validator->passes();
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return __p('photo::phrase.maximum_per_upload_limit_reached', ['limit' => $this->maxFiles]);
    }
}
