<?php

namespace MetaFox\Marketplace\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator;
use MetaFox\Platform\MetaFoxConstant;

class MaximumAttachedPhotosPerUpload implements RuleContract
{
    private string $message;

    public function __construct(protected int $maxFiles = 0)
    {
        $this->message = __p('marketplace::phrase.maximum_per_upload_limit_reached', ['limit' => $this->maxFiles]);
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

        $collect = collect($value)
            ->groupBy('status')
            ->map(function ($item) {
                return count($item);
            });

        $createCount = (int) $collect->get(MetaFoxConstant::FILE_NEW_STATUS);
        $updateCount = (int) $collect->get(MetaFoxConstant::FILE_UPDATE_STATUS);

        $count = $createCount + $updateCount;

        // Require at least one photo
        if ($count < 1) {
            $this->message = __p('marketplace::phrase.attached_photos_is_a_required_field');

            return false;
        }

        /*
         * It means unlimited for uploading
         */
        if ($this->maxFiles == 0) {
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
        return $this->message;
    }
}
