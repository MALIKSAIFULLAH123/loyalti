<?php
namespace MetaFox\Advertise\Http\Requests\v1\Sponsor;

use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Platform\Contracts\Content;

class StoreFeedRequest extends StoreRequest
{
    protected function isFreePrice(?Sponsor $sponsor): bool
    {
        if (null !== $sponsor) {
            return Support::isFreeSponsorInvoice($sponsor);
        }

        $item = $this->getMorphedModel();

        if (null === $item) {
            return false;
        }

        if (!$item->activity_feed instanceof Content) {
            return false;
        }

        if (resolve(SponsorSettingServiceInterface::class)->getPriceForPayment(user(), $item->activity_feed) != 0) {
            return false;
        }

        return true;
    }
}
