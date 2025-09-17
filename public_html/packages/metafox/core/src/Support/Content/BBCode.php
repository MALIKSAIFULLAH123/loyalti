<?php

namespace MetaFox\Core\Support\Content;

/**
 * BBCode syntax processor
 *
 * Class BBCode
 * @package MetaFox\Core\Support\Content
 */
class BBCode implements \MetaFox\Platform\Contracts\BBCode
{
    /**
     * Get all BBCode of text content
     *
     * @param  string  $string
     * @param  string  $code
     *
     * @return array
     */
    public function getAllBBCodeContent(string $string, string $code): array
    {
        $arrContents = [];
        $string = preg_replace_callback("/\[{$code}\](.*?)\[\/{$code}\]/is", function ($matches) use (&$arrContents) {
            $arrContents[] = $matches[1];

            return '';
        }, $string);

        return [$string, $arrContents];
    }
}
