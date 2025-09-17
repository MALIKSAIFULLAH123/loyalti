<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use MetaFox\Form\Builder;
use MetaFox\Form\Section;

class QuoteForm extends CreateForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('forum::form.quote_post'))
            ->action(url_utility()->makeApiUrl('forum-post/quote'))
            ->asPost()
            ->setValue([
                'quote_id' => $this->resource->entityId(),
            ]);
    }

    protected function addMoreBasic(Section $basic): void
    {
        $basic->addFields(
            Builder::hidden('quote_id'),
        );
    }

    protected function addHidden(Section $basic): void
    {
    }
}
