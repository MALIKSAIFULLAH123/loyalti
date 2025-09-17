<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumPost as Model;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateForm.
 * @property ?Model $resource
 */
class CreateForm extends AbstractForm
{
    protected function prepare(): void
    {
        $threadId = 0;

        if (null !== $this->resource) {
            $threadId = $this->resource->thread_id;
        }

        $this
            ->title(__p('forum::form.write_your_reply'))
            ->action('forum-post')
            ->setBackProps(__p('forum::phrase.forums'))
            ->asPost()
            ->setValue([
                'thread_id'   => $threadId,
                'attachments' => [],
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->addMoreBasic($basic);

        $basic->addFields(
            $this->buildTextField(),
            Builder::attachment()
                ->itemType(ForumPost::ENTITY_TYPE),
            Captcha::getFormField('forum.create_post', 'web')
        );

        $this->addHidden($basic);

        $footer = $this->addFooter();

        $this->setButtonFields($footer);
    }

    protected function addMoreBasic(Section $basic): void
    {
    }

    protected function addHidden(Section $basic): void
    {
        $basic->addFields(
            Builder::hidden('thread_id'),
        );
    }

    protected function setButtonFields(Section $footer): void
    {
        $footer->addFields(
            Builder::submit()
                ->label(__p('forum::phrase.send_reply')),
        );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required()
                ->returnKeyType('default')
                ->label(__p('forum::form.content'))
                ->placeholder(__p('forum::form.write_your_reply'))
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable(false),
                );
        }

        return Builder::textArea('text')
            ->required()
            ->returnKeyType('default')
            ->label(__p('forum::form.content'))
            ->placeholder(__p('forum::form.write_your_reply'))
            ->yup(
                Yup::string()
                    ->required()
                    ->nullable(false),
            );
    }
}
