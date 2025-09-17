<?php
namespace MetaFox\Featured\Support;

class Constants
{
    public const USER_ROLE_APPLICABLE_SCOPE_ALL = 'all';
    public const USER_ROLE_APPLICABLE_SCOPE_SPECIFIC = 'specific';

    public const ITEM_APPLICABLE_SCOPE_ALL = 'all';
    public const ITEM_APPLICABLE_SCOPE_SPECIFIC = 'specific';


    public const DURATION_DAY = 'day';
    public const DURATION_MONTH = 'month';
    public const DURATION_YEAR = 'year';
    public const DURATION_ENDLESS = 'endless';

    public const PRICING_OPTION_FREE = 'free';
    public const PRICING_OPTION_CHARGED = 'charged';

    public const STATUS_OPTION_ACTIVE = 'active';
    public const STATUS_OPTION_INACTIVE = 'inactive';


    public const FEATURED_ITEM_STATUS_UNPAID = 'unpaid';
    public const FEATURED_ITEM_STATUS_RUNNING = 'running';
    public const FEATURED_ITEM_STATUS_ENDED = 'ended';
    public const FEATURED_ITEM_STATUS_CANCELLED = 'cancelled';
    public const FEATURED_ITEM_STATUS_PENDING_PAYMENT = 'pending_payment';

    public const PAID_COLOR            = '#31a24a';
    public const UNPAID_COLOR          = '#f4b400';
    public const PENDING_PAYMENT_COLOR = '#4a97dc';
    public const CANCELLED_COLOR       = '#f02848';
}
