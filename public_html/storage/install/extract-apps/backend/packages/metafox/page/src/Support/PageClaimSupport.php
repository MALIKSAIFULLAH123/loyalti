<?php

namespace MetaFox\Page\Support;

use Illuminate\Support\Arr;
use MetaFox\Page\Contracts\PageClaimContract;

class PageClaimSupport implements PageClaimContract
{
    public const STATUS_PENDING = 0;
    public const STATUS_APPROVE = 1;
    public const STATUS_DENY    = 2;
    public const STATUS_CANCEL  = 3;

    public const STATUS_PENDING_TEXT = 'pending';
    public const STATUS_APPROVE_TEXT = 'approved';
    public const STATUS_DENY_TEXT    = 'denied';
    public const STATUS_CANCEL_TEXT  = 'cancelled';

    public const APPROVE_COLOR   = '#31a24a';
    public const PENDING_COLOR   = '#4a97dc';
    public const DENY_COLOR      = '#c46c18';
    public const CANCELLED_COLOR = '#f02848';

    public function getAllowStatusOptions(): array
    {
        return [
            [
                'value' => self::STATUS_PENDING_TEXT,
                'label' => __p('page::phrase.status_pending'),
            ],
            [
                'value' => self::STATUS_APPROVE_TEXT,
                'label' => __p('page::phrase.status_approved'),
            ],
            [
                'value' => self::STATUS_DENY_TEXT,
                'label' => __p('page::phrase.status_denied'),
            ],
            [
                'value' => self::STATUS_CANCEL_TEXT,
                'label' => __p('page::phrase.status_cancelled'),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAllowStatusId(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVE,
            self::STATUS_DENY,
            self::STATUS_CANCEL,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAllowStatus(): array
    {
        return [
            self::STATUS_PENDING_TEXT,
            self::STATUS_APPROVE_TEXT,
            self::STATUS_DENY_TEXT,
            self::STATUS_CANCEL_TEXT,
        ];
    }

    public function getStatusId(string $key): int
    {
        $data = [
            self::STATUS_PENDING_TEXT => self::STATUS_PENDING,
            self::STATUS_APPROVE_TEXT => self::STATUS_APPROVE,
            self::STATUS_DENY_TEXT    => self::STATUS_DENY,
            self::STATUS_CANCEL_TEXT  => self::STATUS_CANCEL,
        ];

        return $data[$key];
    }

    public function getStatusInfo(string $status): ?array
    {
        $infos = $this->getStatusColors();

        return Arr::get($infos, $status);
    }

    protected function getStatusColors(): array
    {
        return [
            self::STATUS_APPROVE => [
                'label' => __p('page::phrase.status_approved'),
                'color' => self::APPROVE_COLOR,
            ],
            self::STATUS_PENDING => [
                'label' => __p('page::phrase.status_pending'),
                'color' => self::PENDING_COLOR,
            ],
            self::STATUS_DENY    => [
                'label' => __p('page::phrase.status_denied'),
                'color' => self::DENY_COLOR,
            ],
            self::STATUS_CANCEL  => [
                'label' => __p('page::phrase.status_cancelled'),
                'color' => self::CANCELLED_COLOR,
            ],
        ];
    }
}
