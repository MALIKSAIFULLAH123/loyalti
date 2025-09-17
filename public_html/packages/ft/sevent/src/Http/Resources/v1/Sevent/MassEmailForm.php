<?php
namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Illuminate\Auth\AuthenticationException;
use Foxexpert\Sevent\Models\Sevent as Model;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * @property Model $resource
 * @preload 1
 */
class MassEmailForm extends AbstractForm
{
    public function boot(
        SeventRepositoryInterface $repository,
        ?int                     $id = null,
    ): void {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $eventId = $this->resource?->id;
        $this->title(__p('sevent::phrase.mass_email_guest'))
            ->action("/sevent/{$eventId}/mass-email")
            ->asPost()
            ->acceptPageParams(['subject', 'text']);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('subject')->required()
                ->label(__p('sevent::phrase.subject'))
                ->placeholder(__p('sevent::phrase.subject'))
                ->yup(Yup::string()
                    ->required()),
            Builder::richTextEditor('text')
                ->label(__p('sevent::phrase.text'))
                ->placeholder(__p('sevent::phrase.text'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
        );
        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('sevent::phrase.send'))
            );
    }
}
