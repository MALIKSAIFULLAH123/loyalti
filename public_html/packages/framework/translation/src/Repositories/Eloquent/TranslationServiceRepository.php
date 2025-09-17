<?php

namespace MetaFox\Translation\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Translation\Models\TranslationGateway;
use MetaFox\Translation\Policies\TranslatePolicy;
use MetaFox\Translation\Repositories\TranslationServiceRepositoryInterface;

class TranslationServiceRepository implements TranslationServiceRepositoryInterface
{
    /**
     * @param string $text
     * @param array  $attributes
     *
     * @return array | null
     * @throws AuthorizationException
     */
    public function translate(string $text, array $attributes = []): array|null
    {
        policy_authorize(TranslatePolicy::class, 'translate');

        $gateway = $this->getAvailableTranslationGateway();

        if (empty($gateway)) {
            return null;
        }

        if (null === $gateway->service_class) {
            return null;
        }

        $serviceClass = $gateway->getService();

        if (!is_object($serviceClass)) {
            return null;
        }

        return $serviceClass->translate($text, $attributes);
    }

    public function checkConfigTranslation(): bool
    {
        if (!policy_check(TranslatePolicy::class, 'translate')) {
            return false;
        }

        $gateway = $this->getAvailableTranslationGateway();

        if (empty($gateway)) {
            return false;
        }

        if (null === $gateway->service_class) {
            return false;
        }

        $serviceClass = $gateway->getService();

        if (!is_object($serviceClass)) {
            return false;
        }

        return $serviceClass->isAvailable();
    }

    private function getAvailableTranslationGateway()
    {
        return TranslationGateway::query()->getModel()
            ->where('is_active', '=', TranslationGateway::IS_ACTIVE)
            ->first();
    }
}
