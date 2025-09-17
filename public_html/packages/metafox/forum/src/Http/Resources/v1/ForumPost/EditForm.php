<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use MetaFox\Form\Section;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */
class EditForm extends CreateForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;
        if (null !== $resource->postText) {
            $text = $resource->postText->text_parsed;
        }

        $values = [
            'text'        => $text,
            'attachments' => $resource->attachmentsForForm(),
        ];

        $this
            ->title(__('core::phrase.edit'))
            ->setBackProps(__p('forum::phrase.forums'))
            ->action('forum-post/' . $resource->entityId())
            ->asPut()
            ->setValue($values);
    }

    protected function addHidden(Section $basic): void
    {
    }

    protected function setButtonFields(Section $footer): void
    {
        $this->addDefaultFooter(true);
    }
}
