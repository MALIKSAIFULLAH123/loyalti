<?php

namespace MetaFox\Platform\Contracts;

interface Input
{
    /**
     * Parse and clean a string. We mainly use this for a title of an item, which
     * does not allow any HTML. It can also be used to shorten a string bassed on
     * the numerical value passed by the 2nd argument.
     *
     * @param string|null $string     $string Text to parse.
     * @param bool        $removeTags (Optional) Should remove html tags or not
     *
     * @return string Returns the new parsed string.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function clean(?string $string = null, bool $removeTags = false, bool $encode = true): string;

    /**
     * Prepare text strings. Used to prepare all data that can contain HTML. Not only does
     * it protect against harmful HTML and CSS, it also has support for BBCode conversion.
     *
     * @param string|null $string - Text to parse.
     *
     * @return string Parsed string.
     */
    public function prepare(?string $string = null): string;

    /**
     * example:
     * data in: ['hashtag', 'nowayhome']
     * data out: #hashtag#nowayhome.
     *
     * @param string[] $hashTags
     * @param bool     $allowSpace
     *
     * @return string
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function extractHashtag(array $hashTags, bool $allowSpace = false): string;

    /**
     * Use for parsing array of tags where each tag may use a comma to separate tags
     * example:
     * data in: ['hashtag, nowayhome', 'homecoming, farfromhome']
     * data out: ['hashtag', 'nowayhome', 'homecoming', 'farfromhome'].
     * @param  array<string> $tags
     * @return array<string>
     */
    public function extractResourceTopic(array $tags = []): array;
}
