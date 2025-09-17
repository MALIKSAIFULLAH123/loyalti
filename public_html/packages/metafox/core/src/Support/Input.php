<?php

namespace MetaFox\Core\Support;

use MetaFox\Platform\MetaFoxConstant;

class Input implements \MetaFox\Platform\Contracts\Input
{
    /**
     * @param  string|null $string
     * @param  bool        $removeTags
     * @param  bool        $encode
     * @return string
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function clean(?string $string = null, bool $removeTags = false, bool $encode = true): string
    {
        $string = $this->prepare($string);

        if ($removeTags === true) {
            $string = htmlspecialchars_decode($string);
            $string = strip_tags($string);
            $string = $this->prepare($string);
        }

        if ($encode === true) {
            $string = htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8', false);
        }

        return $string;
    }

    public function prepare(?string $string = null): string
    {
        if (!is_string($string)) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return trim($string);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function extractHashtag(array $hashTags, bool $allowSpace = false): string
    {
        if (empty($hashTags)) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $prefix = '#';
        if ($allowSpace) {
            $prefix = '##';
        }

        return $prefix . implode($prefix, $hashTags);
    }

    /**
     * @inheritDoc
     */
    public function extractResourceTopic(array $tags = []): array
    {
        if (empty($tags)) {
            return [];
        }

        $result = [];
        $tagString = implode(',', $tags);
        $newTags = explode(',', $tagString);
        foreach ($newTags as $newTag) {
            $trimmed = trim($newTag);

            if (empty($trimmed)) {
                continue;
            }

            $result[] = $trimmed;
        }

        return $result;
    }
}
