<?php

namespace MetaFox\Twilio\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Sms\Contracts\ManagerInterface;
use MetaFox\Sms\Rules\PhoneNumberRule;
use MetaFox\Twilio\Sms\VerifyConfig;
use MetaFox\Yup\Yup;

/**
 * Class ServiceTwilioSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class ServiceTwilioSettingForm extends AbstractForm
{
    /**
     * @var string
     */
    private $namespace = 'sms.services.twilio';

    protected function prepare(): void
    {
        $value = Arr::only(Settings::get($this->namespace), [
            'sid',
            'auth_token',
            'number',
            'test_number',
        ]);

        $this->title(__p('twilio::admin.twilio_settings'))
            ->action('admincp/sms/service/twilio')
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('sid')
                ->required()
                ->autoComplete('off')
                ->label(__p('twilio::admin.account_sid'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('auth_token')
                ->required()
                ->autoComplete('off')
                ->label(__p('twilio::admin.auth_token'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::phoneNumber('number')
                ->marginNormal()
                ->variant('outlined')
                ->sizeMedium()
                ->required()
                ->autoComplete('off')
                ->label(__p('twilio::admin.number'))
                ->placeholder(__p('twilio::admin.number'))
                ->setAttribute('validateParam', 'phone_number')
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::phoneNumber('test_number')
                ->required()
                ->marginNormal()
                ->variant('outlined')
                ->sizeMedium()
                ->autoComplete('off')
                ->label(__p('twilio::admin.test_number'))
                ->placeholder(__p('twilio::admin.test_number'))
                ->setAttribute('validateParam', 'phone_number')
                ->yup(
                    Yup::string()
                        ->required()
                ),
        );

        $this->addDefaultFooter(true);
    }

    /**
     * validated.
     *
     * @param Request $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $data = $request->validate([
            'sid'         => ['required', 'string'],
            'auth_token'  => ['required', 'string'],
            'number'      => ['required', 'string', new PhoneNumberRule()],
            'test_number' => ['required', 'string', new PhoneNumberRule()],
        ]);

        config([
            'sms.services.verify_config' => $data,
        ]);

        /**@var ManagerInterface $smsManager */
        try {
            $smsManager = resolve(ManagerInterface::class);
            $smsManager->service('twilio')
                ->setConfig($data)
                ->send((new VerifyConfig($data))->build());
        } catch (\Throwable $error) {
            abort(422, __p('core::validation.configuration_is_invalid'));
        }

        return [
            $this->namespace => array_merge($data, [
                'service' => 'twilio',
                'is_core' => false,
            ]),
        ];
    }
}
