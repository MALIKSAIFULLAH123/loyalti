<?php

namespace MetaFox\Invite\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Form\Section;
use MetaFox\Invite\Models\Invite;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Yup\Yup;

class InviteRegistrationFieldsListener
{
    public function handle(Section $basic): void
    {
        $isRequired = true;
        $yup        = Yup::string()->required();
        $invite     = $this->getInviteByCode($basic);

        if (!Settings::get('invite.invite_only', false)) {
            if ($this->isHideInviteCodeField($invite)) {
                $basic->addField(Builder::hidden('invite_code'));
                return;
            }

            $isRequired = false;
            $yup        = Yup::string()->nullable();
        }

        $isExpiredInvite = $this->isExpiredInvite($invite);

        $isMobile        = MetaFox::isMobile();

        $expiredWarningField = null;

        if ($isExpiredInvite) {
            $expiredWarningField = match ($isMobile) {
                true     => MobileBuilder::typography(),
                default  => Builder::typography(),
            };

            $expiredWarningField
                ->plainText(__p('invite::validation.the_invite_code_has_expired'))
                ->color('warning.main')
                ->variant('body2')
                ->sx([
                    'mt' => 0,
                ])
                ->showWhen([
                    'and',
                    ['eq', 'invite_code', $invite?->invite_code]
                ]);

            if (!$isMobile) {
                $expiredWarningField->sx([
                    'mt' => 0,
                    'mb' => 1,
                ]);
            }
        }

        if (!$isMobile) {
            $basic->addFields(
                Builder::text('invite_code')
                    ->label(__p('user::phrase.invite_code'))
                    ->placeholder(__p('user::phrase.invite_code'))
                    ->required($isRequired)
                    ->yup($yup),
                $expiredWarningField
            );

            return;
        }

        $field = MobileBuilder::text('invite_code')
            ->label(__p('user::phrase.invite_code'))
            ->placeholder(__p('user::phrase.invite_code'))
            ->required($isRequired)
            ->setAttribute('hasQRScanner', false)
            ->setAttribute('qrScannerParamName', 'invite_code')
            ->yup($yup);

        app('events')->dispatch('invite.override_mobile_invite_code_field', [$field]);

        $basic->addFields(
            $field,
            $expiredWarningField,
        );
    }

    private function isExpiredInvite(?Invite $invite): bool
    {
        if (null === $invite) {
            return false;
        }

        if (null === $invite->expired_at) {
            return false;
        }

        if (Carbon::parse($invite->expired_at)->greaterThan(Carbon::now())) {
            return false;
        }

        return true;
    }

    private function isHideInviteCodeField(?Invite $invite): bool
    {
        if (Settings::get('invite.show_invite_code_on_signup', true)) {
            return false;
        }

        if (null === $invite) {
            return true;
        }

        if ($this->isExpiredInvite($invite)) {
            return false;
        }

        return true;
    }

    private function getInviteByCode(Section $basic): ?Invite
    {
        $form = $basic->getForm();

        if (null === $form) {
            return null;
        }

        $values = $form->getValue() ?: [];

        $inviteCode = Arr::get($values, 'invite_code');

        $code       = Arr::get($values, 'code');

        if (!$inviteCode && !$code) {
            return null;
        }

        $builder = Invite::query();

        if ($code) {
            $builder->where('code', $code);
        }

        if ($inviteCode) {
            $builder->where('invite_code', $inviteCode);
        }

        /**
         * @var Invite|null $invite
         */
        $invite = $builder
            ->where('status_id', Invite::INVITE_PENDING)
            ->first();

        return $invite;
    }
}
