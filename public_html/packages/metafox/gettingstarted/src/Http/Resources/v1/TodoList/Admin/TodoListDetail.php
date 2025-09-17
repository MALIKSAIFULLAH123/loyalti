<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\GettingStarted\Models\TodoList as Model;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class TodoListDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class TodoListDetail extends JsonResource
{
    public function toArray($request)
    {
        $text = parse_output()->getDescription($this->resource->description->text_parsed);
        $text = $this->sanitizeAndDecode($text);

        return [
            'id'          => $this->resource->id,
            'title'       => $this->resource->title,
            'description' => $text,
            'resolution'  => $this->getResolution(),
            'created_at'  => $this->resource->created_at,
            'updated_at'  => $this->resource->updated_at,
            'ordering'    => $this->resource->ordering,
            'links'       => [
                'editItem' => $this->resource->admin_edit_url,
            ],
        ];
    }

    protected function getResolution(): string
    {
        return $this->resource->resolution == MetaFoxConstant::RESOLUTION_WEB
            ? __p('core::phrase.web_resolution_label')
            : __p('core::phrase.mobile_resolution_label');
    }

    protected function sanitizeAndDecode($text)
    {
        $config    = HTMLPurifier_Config::createDefault();
        $purifier  = new HTMLPurifier($config);
        $cleanHtml = $purifier->purify($text);

        return html_entity_decode($cleanHtml);
    }
}
