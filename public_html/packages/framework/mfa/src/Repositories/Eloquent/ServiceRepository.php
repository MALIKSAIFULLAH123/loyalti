<?php

namespace MetaFox\Mfa\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Mfa\Models\Service;
use MetaFox\Mfa\Policies\ServicePolicy;
use MetaFox\Mfa\Repositories\ServiceRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ServiceRepository.
 */
class ServiceRepository extends AbstractRepository implements ServiceRepositoryInterface
{
    public function model()
    {
        return Service::class;
    }

    public function viewServices(User $user): Collection
    {
        policy_authorize(ServicePolicy::class, 'view', $user);

        return $this->getAvailableServices();
    }

    public function getAvailableServices(): Collection
    {
        $query       = $this->getModel()->newQuery();
        $smsService  = Settings::get('sms.default');
        $mailService = Settings::get('mail.default');

        $notServices = [];
        if (empty($smsService) || $smsService == 'log') {
            $notServices[] = Service::SMS_SERVICE;
        }

        if (empty($mailService) || in_array($mailService, ['log', 'array'])) {
            $notServices[] = Service::EMAIL_SERVICE;
        }

        if (!empty($notServices)) {
            $query->whereNotIn('name', $notServices);
        }

        return $query->where('is_active', 1)->get();
    }

    public function getServiceByName(string $name): ?Service
    {
        return $this->getModel()
            ->newQuery()
            ->where('name', '=', $name)
            ->first();
    }

    public function isServiceAvailable(string $name): bool
    {
        $service = $this->getServiceByName($name);

        return $service && $service->is_active;
    }
}
