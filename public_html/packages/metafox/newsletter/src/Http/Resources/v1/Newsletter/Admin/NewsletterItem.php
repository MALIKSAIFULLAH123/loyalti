<?php

namespace MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Newsletter\Models\Newsletter as Model;
use MetaFox\Newsletter\Support\Browse\Traits\ExtraTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class NewsletterItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class NewsletterItem extends JsonResource
{
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $text     = $this->resource->newsletterText?->text;
        $textHtml = $this->resource->newsletterText?->text_html;

        return [
            'id'           => $this->resource->entityId(),
            'user_name'    => $this->resource->userEntity?->display_name,
            'user_link'    => $this->resource->userEntity?->toUrl(),
            'subject'      => $this->resource->subject,
            'created_at'   => $this->resource->created_at,
            'status'       => $this->resource->status,
            'status_text'  => $this->resource->statusText(),
            'process_text' => $this->resource->processText(),
            'text_html'    => $textHtml,
            'text'         => $text,
            'extra'        => $this->getExtra(),
        ];
    }
}
