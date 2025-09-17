<?php

namespace MetaFox\Translation\Support;

use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Translation\Contracts\TranslationGatewayManagerInterface;
use MetaFox\Translation\Http\Resources\v1\TranslationGateway\Admin\GatewayForm;
use MetaFox\Translation\Models\TranslationGateway;

class Translation
{
    public function __construct(
        public DriverRepositoryInterface          $driverRepository,
        public TranslationGatewayManagerInterface $gatewayManager
    ) {}

    public function getGatewayAdminFormById(int $gatewayId): ?GatewayForm
    {
        $gateway = $this->gatewayManager->getGatewayById($gatewayId);
        if (!$gateway instanceof TranslationGateway) {
            return null;
        }

        $form = $this->getGatewayAdminFormByName("{$gateway->service}.gateway.form");

        if (!$form instanceof GatewayForm) {
            // default admin gateway form
            $form = $this->getGatewayAdminFormByName('translation.gateway.form');
        }

        $form?->boot($gatewayId);

        return $form;
    }

    public function getGatewayAdminFormByName(string $formName): ?GatewayForm
    {
        $driver = $this->driverRepository
            ->getDriver(Constants::DRIVER_TYPE_FORM, $formName, 'admin');

        /** @var ?GatewayForm $form */
        $form = resolve($driver);

        return $form;
    }
}
