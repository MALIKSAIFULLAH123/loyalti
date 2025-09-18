<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite;

use Carbon\CarbonInterval;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants;
use MetaFox\Invite\Models\Invite as Model;
use MetaFox\Invite\Policies\InvitePolicy;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Invite\Support\Form\InviteCodeField;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreInviteForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreInviteForm extends AbstractForm
{
    protected InviteRepositoryInterface $repository;

    protected ?string $timeUnlock = null;
    protected string  $linkInvite;
    protected string  $code;

    /**
     * @param string|null $timeUnlock
     */
    public function setTimeUnlock(?string $timeUnlock): void
    {
        $this->timeUnlock = $timeUnlock;
    }

    /**
     * @param InviteRepositoryInterface     $repository
     * @param InviteCodeRepositoryInterface $codeRepository
     *
     * @return void
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(InviteRepositoryInterface $repository, InviteCodeRepositoryInterface $codeRepository): void
    {
        $context          = user();
        $this->repository = $repository;
        $inviteCode       = $codeRepository->getUserCode($context);
        $this->linkInvite = $inviteCode->toLinkInvite();
        $this->code       = $inviteCode->code;

        policy_authorize(InvitePolicy::class, 'create', $context);
    }

    protected function prepare(): void
    {
        $this->title(__p('invite::phrase.invite_your_friends'))
            ->action('/invite')
            ->setBackProps(__p('invite::phrase.invitations'))
            ->asPost()
            ->updateResponseValue()
            ->secondAction('invite/redirectManage')
            ->setValue([
                'link_invite' => $this->linkInvite,
                'invite_code' => $this->code,
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic      = $this->addBasic();
        $minContact = 1;

        $basic->addFields(
            Builder::copyText('link_invite')
                ->label(__p('invite::phrase.link_invite'))
                ->readOnly(),
            (new InviteCodeField())
                ->name('invite_code')
                ->label(__p('user::phrase.invite_code'))
                ->setAttribute('hasRefresh', true)
                ->setAttribute('action', [
                    'confirm' => [
                        'title'   => __p('core::phrase.confirm'),
                        'message' => 'refresh_invite_code_confirm',
                    ],
                    'url'     => 'invite-code/refresh',
                    'method'  => Constants::METHOD_PATCH,
                ])
                ->readOnly(),
            $this->getInfoInviteField(),
            Builder::tags('recipients')
                ->disableSuggestion()
                ->allowSpaceNewTag()
                ->label(__p('invite::phrase.contacts'))
                ->placeholder(__p('invite::phrase.add_emails_or_phone_numbers'))
                ->description(__p('invite::phrase.separate_multiple_emails_phone_numbers_with_commas_or_enter_or_tab'))
                ->required()
                ->yup(
                    Yup::array()
                        ->required()
                        ->min($minContact)
                        ->setError('min', __p(
                            'validation.min.array',
                            ['attribute' => __p('invite::phrase.contacts'), 'min' => $minContact]
                        ))
                ),
            Builder::textArea('message')
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->label(__p('invite::phrase.add_personal_message'))
                ->placeholder(__p('invite::phrase.write_message'))
                ->yup(Yup::string()
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('invite::phrase.send_invitation'))
                    ->disabled($this->isDisable()),
                Builder::cancelButton(),
            );
    }

    /**
     * @throws AuthenticationException
     */
    protected function isDisable(): bool
    {
        $context     = user();
        $waitMinutes = $context->getPermissionValue('invite.must_wait_minutes_until_are_allowed');

        if ($waitMinutes == 0) {
            return false;
        }

        $now = Carbon::now()->subMinutes($waitMinutes);

        $invite = $this->repository->getModel()
            ->newQuery()
            ->where('user_id', $context->entityId())
            ->where('created_at', '>=', $now->toDateTimeString())
            ->first();

        if (!$invite instanceof Model) {
            return false;
        }

        $minutes      = (Carbon::make($invite->created_at)->timestamp - $now->timestamp) / 60;
        $totalMinutes = CarbonInterval::make($minutes . 'm')->totalMinutes;
        $timeUnlock   = CarbonInterval::minutes($totalMinutes);

        $this->setTimeUnlock($timeUnlock);

        return true;
    }

    /**
     * @return AbstractField
     * @throws AuthenticationException
     */
    protected function getInfoInviteField(): AbstractField
    {
        $field      = Builder::alert('alert_info_invite')->asInfo();
        $makeFriend = (int) Settings::get('invite.make_invited_users_friends_with_their_host');
        $typography = __p('invite::phrase.invite_your_friends_to_site', [
            'value'      => config('app.name'),
            'makeFriend' => $makeFriend,
        ]);

        if ($this->isDisable()) {
            $typography = __p('invite::phrase.you_must_will_to_wait_minutes_before_can_send_another_invitation', [
                'value' => $this->timeUnlock,
            ]);

            $field->asWarning();
        }

        return $field->message($typography);
    }
}
