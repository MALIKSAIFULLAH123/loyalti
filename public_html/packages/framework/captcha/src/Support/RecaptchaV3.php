<?php

namespace MetaFox\Captcha\Support;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use MetaFox\Captcha\Contracts\CaptchaContract;
use MetaFox\Captcha\Rules\ReCaptchaV3Rule;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use Mews\Captcha\Facades\Captcha;

class RecaptchaV3 implements CaptchaContract
{
    /**
     * @var string|mixed
     */
    protected ?string $secret = null;

    /**
     * @var string|mixed
     */
    protected ?string $siteKey = null;

    /**
     * @var Client
     */
    protected Client $http;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var float
     */
    protected float $minScore = 0.5;

    /**
     * RecaptchaV3 constructor.
     *
     * @param Client  $client
     * @param Request $request
     */
    public function __construct(Client $client, Request $request)
    {
        $settings       = Settings::get('captcha.recaptcha_v3', []);
        $this->secret   = Arr::get($settings, 'secret');
        $this->siteKey  = Arr::get($settings, 'site_key');
        $this->http     = $client;
        $this->request  = $request;
        $this->minScore = Arr::get($settings, 'min_score', 0.5);
    }

    /**
     * @param  string      $token
     * @param  string|null $action
     * @param  string|null $publicKey
     * @return bool
     */
    public function verify(string $token, ?string $action = null, ?string $publicKey = null): bool
    {
        if (null !== $action) {
            if (!Settings::get('captcha.rules.' . $action)) {
                return false;
            }
        }

        try {
            $data = [
                'secret'   => $this->secret,
                'response' => $token,
            ];

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', $data);

            $body = $response->json();

            if (!is_array($body)) {
                return false;
            }

            $responseScore = Arr::get($body, 'score');

            if (is_numeric($responseScore)) {
                //Min Score is used to check trust from verifying token. It means 1.0 is very likely good interaction, 0.0 is very badly (Maybe bot)
                if ($this->minScore > $responseScore) {
                    return false;
                }
            }

            return Arr::get($body, 'success', false);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Get rule of actions and others name.
     *
     * @param string|null $actionName
     *
     * @return array|null
     */
    public function ruleOf(?string $action = null): array
    {
        if (!Settings::get('captcha.rules.' . $action)) {
            return ['sometimes', 'nullable'];
        }

        if (!$this->secret) {
            return ['sometimes', 'nullable'];
        }

        if (!$this->siteKey) {
            return ['sometimes', 'nullable'];
        }

        return ['required', new ReCaptchaV3Rule($action)];
    }

    public function errorMessage(?string $action = null): string
    {
        return __p('validation.recaptcha_v3');
    }

    public function ruleMessage(?string $action = null, string $name = 'captcha'): array
    {
        $message = __p('validation.recaptcha_v3');

        return [
            $name . '.required' => $message,
        ];
    }

    public function generateFormField(?string $action = null, string $resolution = 'web', bool $isPreload = false, string $fieldName = 'captcha', bool $isHidden = true): ?AbstractField
    {
        if (null === $this->secret) {
            return null;
        }

        if (null === $this->siteKey) {
            return null;
        }

        $driver = resolve(DriverRepositoryInterface::class)->getDriver(Constants::DRIVER_TYPE_FORM_FIELD, 'recaptchaV3', $resolution);

        $field = resolve($driver);

        return $field->name($fieldName)
            ->actionName($action)
            ->siteKey($this->siteKey);
    }

    public function refresh(): array
    {
        return [];
    }
}
