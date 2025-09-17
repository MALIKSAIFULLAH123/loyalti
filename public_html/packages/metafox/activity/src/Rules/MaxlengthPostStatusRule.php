<?php

namespace MetaFox\Activity\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Platform\Facades\Settings;

class MaxlengthPostStatusRule implements Rule
{
    public function passes($attribute, $value)
    {
        $maxCharacters = $this->getSettings();

        if ($maxCharacters == 0) {
            return true;
        }

        $valueStripTag = $value;

        foreach ($this->getPatterns() as $item) {
            $valueStripTag = preg_replace_callback($item, function ($params) {
                [, , $oldName] = $params;

                return $oldName;
            }, $valueStripTag);
        }

        return mb_strlen($valueStripTag, 'UTF-8') <= $maxCharacters;
    }

    public function message()
    {
        return __p('activity::validation.max_characters_for_post_status', ['max' => $this->getSettings()]);
    }

    protected function getPatterns(): array
    {
        $patterns = app('events')->dispatch('core.mention.pattern');
        $results  = [];
        foreach ($patterns as $pattern) {
            if (!is_array($pattern)) {
                continue;
            }

            foreach ($pattern as $item) {
                $results[] = $item;
            }
        }

        return $results;
    }

    protected function getSettings(): int
    {
        return Settings::get('activity.feed.maximum_characters_for_post_status', 0);
    }
}
