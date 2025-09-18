<?php

namespace MetaFox\Subscription\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;
use MetaFox\User\Models\User;

/*
 * stub: packages/database/json-importer.stub
 */

class SubscriptionInvoiceImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'package_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$package', 'payment_gateway']);

        $this->remapCurrency();

        $this->remapPaymentStatus();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'                     => $entry['$oid'] ?? null,
            'user_id'                => $entry['user_id'] ?? null,
            'user_type'              => $entry['user_type'] ?? null,
            'renew_type'             => $entry['renew_type'] ?? null,
            'recurring_price'        => $entry['recurring_price'] ?? null,
            'initial_price'          => $entry['initial_price'] ?? 0.00,
            'package_id'             => $entry['package_id'] ?? null,
            'payment_gateway'        => $entry['payment_gateway_id'] ?? 0,
            'currency'               => $entry['currency_id'] ?? null,
            'payment_status'         => $entry['payment_status'] ?? null,
            'is_canceled_by_gateway' => $entry['is_canceled_by_gateway'] ?? false,
            'created_at'             => $entry['created_at'] ?? null,
            'activated_at'           => $entry['activated_at'] ?? null,
            'expired_at'             => $entry['expired_at'] ?? null,
            'notified_at'            => $entry['notified_at'] ?? null,
        ]);
    }

    private function remapPaymentStatus(): void
    {
        $statusList = Helper::getPaymentStatus();

        foreach ($this->entries as &$entry) {
            $status = $entry['payment_status'] ?? null;

            if (!$status || !in_array($status, $statusList)) {
                $entry['payment_status'] = Helper::getInitPaymentStatus();
                continue;
            }

            if ($status != Helper::getCompletedPaymentStatus()) {
                continue;
            }

            $this->handleCompletedStatus($entry);
        }
    }

    private function handleCompletedStatus(array &$entry): void
    {
        $packageId = $entry['package_id'] ?? null;
        $package   = SubscriptionPackage::query()->where('id', '=', $packageId)->first();
        if (!$package instanceof SubscriptionPackage) {
            return;
        }

        $userId = $entry['user_id'] ?? null;
        $user   = User::query()->where('id', '=', $userId)->first();
        if (!$user instanceof User) {
            return;
        }

        $upgradedRole = $package->upgraded_role_id;
        $currentRole  = $user->roleId();

        if ($upgradedRole == $currentRole) {
            return;
        }

        $entry['payment_status'] = Helper::getCanceledPaymentStatus();
    }
}
