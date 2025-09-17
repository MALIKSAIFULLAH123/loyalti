<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Ticket;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Foxexpert\Sevent\Http\Requests\v1\Ticket\CreateFormRequest;
use Foxexpert\Sevent\Models\Ticket as Model;
use Foxexpert\Sevent\Repositories\TicketRepositoryInterface;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;
use Illuminate\Support\Carbon;
/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreTicketForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreTicketForm extends AbstractForm
{
    use PrivacyFieldTrait;

    public bool $preserveKeys = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, TicketRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();

        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $this->title(__p('sevent::phrase.add_new_ticket'))
            ->action(url_utility()->makeApiUrl('sevent/ticket'))
            ->asPost()
            ->setBackProps(__p('core::web.sevent'))
            ->setValue([
                'title'       => '',
                'module_id'   => 'sevent',
                'expiry_date'    => Carbon::now()->addWeek()->toISOString(),
                'is_unlimited' => 1,
                'sevent_id'    => $this->resource->sevent_id
            ]);
    }

    protected function getDisplayFormat(int $value): string
    {
        $displayFormat = [
            12 => MetaFoxConstant::DISPLAY_FORMAT_TIME_12,
            24 => MetaFoxConstant::DISPLAY_FORMAT_TIME_24,
        ];

        return $displayFormat[$value];
    }

    protected function initialize(): void
    {
        $basic              = $this->addBasic();
        $timeFormat = Settings::get('sevent.time_format');

        $basic->addFields(
            Builder::text('title')
                ->required()
                ->marginNormal()
                ->label(__p('sevent::phrase.ticket_field'))
                ->placeholder(__p('sevent::phrase.ticket_field'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('amount')
                ->marginNormal()
                ->required()
                ->label(__p('sevent::phrase.price'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::richTextEditor('description')
                ->required()
                ->label(__p('sevent::phrase.ticket_description_field'))
                ->placeholder(__p('sevent::phrase.add_some_content_to_your_sevent'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('qty')
                ->marginNormal()
                ->label(__p('sevent::phrase.qty_field'))
                ->placeholder(__p('sevent::phrase.qty_field'))
                ,
            Builder::switch('is_unlimited')
                ->label(__p('sevent::phrase.is_unlimited_qty')),
            Builder::date('expiry_date')
                ->returnKeyType('next')
                ->displayFormat($this->getDisplayFormat($timeFormat))
                ->label(__p('sevent::phrase.expiry_date'))
                ,
            Builder::hidden('sevent_id'),
            Builder::hidden('owner_id'),
        );
        
        $this->addFooter()
            ->addFields(
                $this->buildPublishButton(),
                Builder::cancelButton()
                    ->sizeMedium(),
            );

        // force returnUrl as string
        $basic->addField(
            Builder::hidden('returnUrl')
        );
    }

    protected function buildPublishButton(): AbstractField
    {
        return Builder::submit()
            ->label(__p('core::phrase.publish'))
            ->flexWidth(true);
    }

    protected function buildSaveAsDraftButton(): AbstractField
    {
        return Builder::submit('draft')
            ->label(__p('core::phrase.save_as_draft'))
            ->color('primary')
            ->setValue(1)
            ->variant('outlined')
            ->showWhen(['falsy', 'published']);
    }

    protected function buildTagField(): ?AbstractField
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return null;
        }

        return Builder::tags()
            ->label(__p('core::phrase.topics'))
            ->placeholder(__p('core::phrase.keywords'));
    }
}
