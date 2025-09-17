<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Forum\Models\ForumPost as Model;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * Class CreateMobileForm.
 * @property Model $resource
 */
class EditMobileForm extends AbstractForm
{
    public function boot(ForumPostRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

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

        $this->title(__('forum::phrase.edit_post'))
            ->setBackProps(__p('forum::phrase.forums'))
            ->action('forum-post/' . $resource->entityId())
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->addMoreBasic($basic);

        $basic->addFields(
            $this->buildTextField(),
        );

        $this->addHidden($basic);
    }

    protected function addHidden(Section $basic): void
    {
        $basic->addFields(
            Builder::hidden('thread_id'),
        );
    }

    protected function addMoreBasic(Section $basic): void
    {
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
