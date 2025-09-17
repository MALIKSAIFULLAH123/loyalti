<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumPost as Model;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * Class CreateForm.
 * @property ?Model $resource
 */
class CreateMobileForm extends AbstractForm
{
    private $threadResource;

    public function boot(ForumThreadRepositoryInterface $repository, ?int $id = null): void
    {
        $this->threadResource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $threadId = 0;

        if (null !== $this->threadResource) {
            $threadId = $this->threadResource->entityId();
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
        );

        $this->addHidden($basic);
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
