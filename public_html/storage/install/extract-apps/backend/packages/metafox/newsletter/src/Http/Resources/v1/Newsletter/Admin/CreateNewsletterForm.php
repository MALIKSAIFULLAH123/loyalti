<?php

namespace MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin;

use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Newsletter\Models\Newsletter as Model;
use MetaFox\Newsletter\Notifications\NewsletterNotification;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\UserRole;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateNewsletterForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateNewsletterForm extends AbstractForm
{
    protected bool $viewOnly = false;

    protected function prepare(): void
    {
        $this->title(__p('newsletter::phrase.create_newsletter'))
            ->action(apiUrl('admin.newsletter.newsletter.store'))
            ->asPost()
            ->setValue([
                'archive'      => 1,
                'round'        => 50,
                'channel_mail' => 1,
                'channel_sms'  => 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::checkbox('archive')
                    ->label(__p('newsletter::phrase.archive'))
                    ->setAttribute('readOnly', $this->viewOnly)
                    ->disabled($this->viewOnly)
                    ->description(__p('newsletter::phrase.archive_desc')),
                Builder::checkbox('override_privacy')
                    ->label(__p('newsletter::phrase.override_privacy'))
                    ->disabled($this->viewOnly)
                    ->description(__p('newsletter::phrase.override_privacy_desc')),
                Builder::choice('roles')
                    ->multiple()
                    ->disabled($this->viewOnly)
                    ->readOnly()
                    ->label(__p('newsletter::phrase.user_roles'))
                    ->options($this->getRoleOptions()),
                Builder::choice('countries')
                    ->multiple()
                    ->disabled($this->viewOnly)
                    ->label(__p('newsletter::phrase.locations'))
                    ->options(Country::buildCountrySearchForm()),
                Builder::gender('genders')
                    ->label(__p('newsletter::phrase.genders'))
                    ->disabled($this->viewOnly)
                    ->multiple()
                    ->options($this->getGenderOptions()),
                Builder::choice('age_from')
                    ->label(__p('newsletter::phrase.age_from'))
                    ->options($this->getAgeOptions())
                    ->disabled($this->viewOnly)
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->unint()
                            ->min(1, __p('newsletter::validation.age_from_must_be_greater_than_or_equal_to_number', ['number' => 1]))
                    ),
                Builder::choice('age_to')
                    ->label(__p('newsletter::phrase.age_to'))
                    ->disabled($this->viewOnly)
                    ->options($this->getAgeOptions())
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->unint()
                            ->when(
                                Yup::when('age_from')
                                    ->is('$exists')
                                    ->then(
                                        Yup::number()
                                            ->nullable()
                                            ->unint()
                                            ->min(['ref' => 'age_from'])
                                    )
                            )
                    ),
                Builder::text('round')
                    ->required()
                    ->disabled($this->viewOnly)
                    ->asNumber()
                    ->label(__p('newsletter::phrase.how_many_per_round'))
                    ->yup(
                        Yup::number()
                            ->required()
                            ->int()
                            ->unint()
                            ->positive()
                            ->min(1)
                            ->max(1000)
                            ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                    ),
                Builder::translatableText('subject')
                    ->disabled($this->viewOnly)
                    ->required()
                    ->label(__p('newsletter::phrase.subject'))
                    ->yup(Yup::string()->required())
                    ->buildFields(),
            );

        $section = $this->addSection(['name' => 'preferred_channels'])
            ->label(__p('newsletter::phrase.preferred_channels'))
            ->description(__p('newsletter::phrase.preferred_channels_desc'));
        $default = Language::getDefaultLocaleId();

        $this->buildTextHtmlField($section, $default);
        $this->buildTextField($section, $default);

        if (!$this->viewOnly) {
            $this->addDefaultFooter();
        }
    }

    protected function getRoleOptions(): array
    {
        return resolve(RoleRepositoryInterface::class)
            ->getRoleOptionsWithout([UserRole::SUPER_ADMIN_USER]);
    }

    protected function getAgeOptions(): array
    {
        $minAge = 6;
        $maxAge = 123;

        return array_map(function (int $value) {
            return [
                'label' => $value,
                'value' => $value,
            ];
        }, range($minAge, $maxAge));
    }

    protected function getGenderOptions(): array
    {
        return resolve(UserGenderRepositoryInterface::class)->getGenderOptions();
    }

    protected function getWarningMessage(string $channel): string
    {
        $type = (new NewsletterNotification())->getType();
        /**
         * @var TypeRepositoryInterface $repository
         */
        $repository       = resolve(TypeRepositoryInterface::class);
        $notificationType = $repository->getNotificationTypeByType($type);
        $message          = MetaFoxConstant::EMPTY_STRING;

        if ($notificationType === null) {
            return $message;
        }

        $isExists = $notificationType->typeChannels()
            ->where('channel', $channel)
            ->where('is_active', 1)
            ->exists();

        $channelLabel = match ($channel) {
            'sms'  => __p('sms::phrase.sms'),
            'mail' => __p('mail::phrase.mail'),
        };

        if (!$isExists) {
            $message = __P('newsletter::phrase.the_channel_is_currently_disabled_for_notification', [
                'channel'      => $channelLabel,
                'notification' => __p($notificationType->title),
                'link'         => url_utility()->makeApiFullUrl('admincp/notification/type/browse?module_id=newsletter'),
            ]);
        }

        return $message;
    }

    protected function buildTextHtmlField(Section $section, string $defaultLanguage): void
    {
        $section->addFields(
            Builder::switch('channel_mail')
                ->warning($this->getWarningMessage('mail'))
                ->disabled($this->viewOnly)
                ->requiredWhen(['eq', 'channel_sms', 0])
                ->label(__p('mail::phrase.mail')),

            Builder::translatableText('text_html')
                ->asTextEditor()
                ->label(__p('newsletter::phrase.html_content'))
                ->requiredWhen(['eq', 'channel_mail', 1])
                ->showWhen(['eq', 'channel_mail', 1])
                ->validation(Yup::object()
                    ->when(
                        Yup::when('channel_mail')
                            ->is(1)
                            ->then(Yup::object()
                                ->required()
                                ->addProperty($defaultLanguage, Yup::string()->required(__p('newsletter::validation.required_if', [
                                    'attribute' => __p('newsletter::phrase.text_content'),
                                    'other'     => __p('mail::phrase.mail'),
                                ]))))
                    )
                    ->addProperty($defaultLanguage, Yup::string())
                )
                ->disabled($this->viewOnly)
                ->buildFields(),
        );
    }

    protected function buildTextField(Section $section, string $defaultLanguage): void
    {
        $section->addFields(
            Builder::switch('channel_sms')
                ->requiredWhen(['eq', 'channel_mail', 0])
                ->disabled($this->viewOnly)
                ->warning($this->getWarningMessage('sms'))
                ->label(__p('sms::phrase.sms')),

            Builder::translatableText('text')
                ->asTextArea()
                ->showWhen(['eq', 'channel_sms', 1])
                ->yup(Yup::string()->nullable())
                ->label(__p('newsletter::phrase.text_content'))
                ->validation(Yup::object()
                    ->when(
                        Yup::when('channel_sms')
                            ->is(1)
                            ->then(Yup::object()
                                ->required()
                                ->addProperty($defaultLanguage, Yup::string()->required(__p('newsletter::validation.required_if', [
                                    'attribute' => __p('newsletter::phrase.text_content'),
                                    'other'     => __p('sms::phrase.sms'),
                                ]))))
                    )
                    ->addProperty($defaultLanguage, Yup::string())
                )
                ->disabled($this->viewOnly)
                ->buildFields(),
        );
    }
}
