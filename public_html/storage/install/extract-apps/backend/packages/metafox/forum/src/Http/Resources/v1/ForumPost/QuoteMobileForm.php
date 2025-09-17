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
 * Class CreateForm.
 * @property ?Model $resource
 */
class QuoteMobileForm extends AbstractForm
{
    public function boot(ForumPostRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

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

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->addMoreBasic($basic);

        $basic->addFields(
            Builder::information('quote_id')
                ->label(__p('forum::form.quote_content')),
            $this->buildTextField(),
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
